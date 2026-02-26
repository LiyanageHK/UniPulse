<?php

namespace App\Http\Controllers;

use App\Models\PeerGroup;
use App\Models\PeerRequest;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PeerMatchingController extends Controller
{
    /**
     * Show the ML peer matching form and any existing results.
     */
    public function index(Request $request)
    {
        // On browser refresh (?clear=1), wipe previous results
        if ($request->query('clear')) {
            session()->forget(['peer_purpose', 'peer_group_size', 'peer_matches']);
        }

        $purpose   = session('peer_purpose',   $request->query('purpose'));
        $groupSize = (int) session('peer_group_size', $request->query('group_size', 4));
        $matches   = collect(session('peer_matches', []));

        // Load request statuses between the logged-in user and each matched user
        $authId   = auth()->id();
        $matchIds = $matches->pluck('user_id')->filter()->values();

        $sentRequests = PeerRequest::where('sender_id', $authId)
            ->whereIn('receiver_id', $matchIds)
            ->get()
            ->keyBy('receiver_id');

        $requestStatuses = $sentRequests->map(fn($r) => $r->status);
        $sentRequestIds  = $sentRequests->map(fn($r) => $r->id);

        // Also capture requests they sent TO us
        $incomingStatuses = PeerRequest::where('receiver_id', $authId)
            ->whereIn('sender_id', $matchIds)
            ->get()
            ->keyBy('sender_id')
            ->map(fn($r) => $r->status);

        return view('dashboard.groups.peer-matching',
            compact('matches', 'purpose', 'groupSize', 'requestStatuses', 'sentRequestIds', 'incomingStatuses'));
    }

    /**
     * Find the best-matching group for the logged-in user.
     */
    public function findMyGroup(Request $request)
    {
        $request->validate([
            'purpose'    => 'required|in:default,academic,hobby,personality',
            'group_size' => 'required|integer|min:2|max:20',
        ]);

        $purpose   = $request->input('purpose');
        $groupSize = (int) $request->input('group_size');
        $userId    = auth()->id();

        $profileCount = StudentProfile::count();
        if ($profileCount < 2) {
            return back()->withInput()
                ->withErrors(['generate' => 'Not enough student profiles to find matches (need at least 2).']);
        }

        try {
            $mlUrl = config('services.ml_clustering.url', 'http://127.0.0.1:5000');

            $response = Http::timeout(30)->post("{$mlUrl}/find-my-group", [
                'user_id'    => $userId,
                'purpose'    => $purpose,
                'group_size' => $groupSize,
            ]);

            if ($response->failed()) {
                Log::error('ML find-my-group API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return back()->withInput()
                    ->withErrors(['generate' => 'ML service error (HTTP ' . $response->status() . '). Ensure the AI service is running.']);
            }

            $data = $response->json();

            if (!isset($data['matches'])) {
                return back()->withInput()
                    ->withErrors(['generate' => 'ML service returned an unexpected response.']);
            }

            // Enrich matches with User models
            $userMap = User::whereIn('id', collect($data['matches'])->pluck('user_id'))
                ->get()->keyBy('id');

            $matches = collect($data['matches'])->map(function ($m) use ($userMap) {
                $m['user'] = $userMap->get($m['user_id']);
                return $m;
            })->toArray();

            session([
                'peer_matches'    => $matches,
                'peer_purpose'    => $purpose,
                'peer_group_size' => $groupSize,
            ]);

            return redirect()->route('peer-matching.index');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->withInput()
                ->withErrors(['generate' => 'Could not connect to the ML service. Ensure the Python AI service is running.']);
        } catch (\Exception $e) {
            Log::error('findMyGroup failed', ['error' => $e->getMessage()]);
            return back()->withInput()
                ->withErrors(['generate' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate peer groups via the Python ML clustering API.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'purpose'    => 'required|in:study,sports,social',
            'group_size' => 'required|integer|min:2|max:10',
        ]);

        $purpose   = $request->input('purpose');
        $groupSize = (int) $request->input('group_size');

        // Ensure we have enough students with onboarding profiles
        $profileCount = StudentProfile::count();

        if ($profileCount < 4) {
            return back()
                ->withInput()
                ->withErrors(['generate' => 'Not enough students with completed profiles to form groups (minimum 4 required, found ' . $profileCount . ').']);
        }

        try {
            // Call the Python ML microservice
            $mlUrl = config('services.ml_clustering.url', 'http://127.0.0.1:5000');

            $response = Http::timeout(30)->post("{$mlUrl}/run-clustering", [
                'group_size' => $groupSize,
                'purpose'    => $purpose,
            ]);

            if ($response->failed()) {
                Log::error('ML Clustering API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['generate' => 'ML service returned an error (HTTP ' . $response->status() . '). Please ensure the AI service is running.']);
            }

            $data = $response->json();

            // Validate API response structure
            if (!isset($data['cluster_assignments']) || !isset($data['user_ids'])) {
                Log::error('ML Clustering API invalid response', ['data' => $data]);

                return back()
                    ->withInput()
                    ->withErrors(['generate' => 'ML service returned an unexpected response format.']);
            }

            $clusterAssignments = $data['cluster_assignments'];
            $userIds            = $data['user_ids'];
            $clustersCount      = $data['clusters_count'] ?? max($clusterAssignments) + 1;

            if (count($clusterAssignments) !== count($userIds)) {
                return back()
                    ->withInput()
                    ->withErrors(['generate' => 'ML service returned mismatched data. Please try again.']);
            }

            // Store results in a transaction
            DB::beginTransaction();

            try {
                // Remove previous groups for this purpose
                PeerGroup::where('purpose', $purpose)->delete();

                // Build records for bulk insert
                $records = [];
                $now     = now();

                for ($i = 0; $i < count($userIds); $i++) {
                    $clusterId = $clusterAssignments[$i];

                    $records[] = [
                        'cluster_id'  => $clusterId,
                        'user_id'     => $userIds[$i],
                        'purpose'     => $purpose,
                        'group_name'  => ucfirst($purpose) . ' Group ' . ($clusterId + 1),
                        'created_at'  => $now,
                    ];
                }

                // Bulk insert in chunks for performance
                foreach (array_chunk($records, 500) as $chunk) {
                    PeerGroup::insert($chunk);
                }

                DB::commit();

                // Retrieve the freshly created groups
                $groups = PeerGroup::getGroupedByCluster($purpose);

                return redirect()
                    ->route('peer-matching.index', ['purpose' => $purpose])
                    ->with('success', "Successfully generated {$clustersCount} groups for {$purpose} from {$data['total_students']} students!");

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to save peer groups', ['error' => $e->getMessage()]);

                return back()
                    ->withInput()
                    ->withErrors(['generate' => 'Failed to save groups: ' . $e->getMessage()]);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('ML Clustering API connection failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['generate' => 'Could not connect to the ML service. Please ensure the Python AI service is running on ' . config('services.ml_clustering.url', 'http://127.0.0.1:5000')]);

        } catch (\Exception $e) {
            Log::error('Peer matching generation failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['generate' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }
}
