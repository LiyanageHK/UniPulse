<?php
// app/Http/Controllers/GroupController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // List all groups
    public function index()
    {
        $myGroups = Auth::user()->groups()->with('admin', 'members')->get();
        $categories = ['Technology', 'Sports', 'Music', 'Art', 'Gaming', 'Study', 'Other'];
        
        return view('dashboard.groups.index', compact('myGroups', 'categories'));
    }

    // Discover groups
    public function discover(Request $request)
    {
        $userId = Auth::id();
        
        $query = Group::whereDoesntHave('members', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('admin', 'members');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $groups = $query->get();
        $categories = ['Technology', 'Sports', 'Music', 'Art', 'Gaming', 'Study', 'Other'];

        return view('dashboard.groups.discover', compact('groups', 'categories'));
    }

    // Show create group form
    public function create()
    {
        $categories = ['Technology', 'Sports', 'Music', 'Art', 'Gaming', 'Study', 'Other'];
        return view('dashboard.groups.create', compact('categories'));
    }

    // Store new group
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $group = Group::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category,
                'admin_id' => Auth::id(),
            ]);

            // Add creator as first member
            $group->members()->attach(Auth::id(), ['joined_at' => now()]);

            DB::commit();

            return redirect()->route('groups.show', $group->id)
                           ->with('success', 'Group created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create group. Please try again.']);
        }
    }

    // Show single group
    public function show($id)
    {
        $group = Group::with('admin', 'members', 'pendingRequests.user')->findOrFail($id);
        $userId = Auth::id();

        // Check if user is member
        $isMember = $group->isMember($userId);
        $isAdmin = $group->isAdmin($userId);

        // Check if user has pending request
        $hasPendingRequest = GroupRequest::where('group_id', $id)
                                        ->where('user_id', $userId)
                                        ->where('status', 'pending')
                                        ->exists();

        if (!$isMember) {
            return view('dashboard.groups.preview', compact('group', 'hasPendingRequest'));
        }

        // Get available users to invite (peers who are not members)
        $availableUsers = User::whereDoesntHave('groups', function($q) use ($id) {
            $q->where('groups.id', $id);
        })->where('id', '!=', $userId)->get();

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');

        return view('dashboard.groups.show', compact('group', 'isAdmin', 'availableUsers', 'supabaseUrl', 'supabaseKey'));
    }

    // Send join request
    public function sendRequest($groupId)
    {
        $userId = Auth::id();
        $group = Group::findOrFail($groupId);

        // Check if already member
        if ($group->isMember($userId)) {
            return back()->with('error', 'You are already a member of this group.');
        }

        // Check if request already exists
        if (GroupRequest::where('group_id', $groupId)->where('user_id', $userId)->exists()) {
            return back()->with('error', 'You have already sent a request to this group.');
        }

        GroupRequest::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Join request sent successfully!');
    }

    // Accept join request (admin only)
    public function acceptRequest($requestId)
    {
        $request = GroupRequest::findOrFail($requestId);
        $group = $request->group;

        // Check if user is admin
        if (!$group->isAdmin(Auth::id())) {
            return back()->with('error', 'Only admin can accept requests.');
        }

        DB::beginTransaction();

        try {
            // Add user to group
            $group->members()->attach($request->user_id, ['joined_at' => now()]);

            // Update request status
            $request->update(['status' => 'accepted']);

            DB::commit();

            return back()->with('success', 'Request accepted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to accept request.');
        }
    }

    // Reject join request (admin only)
    public function rejectRequest($requestId)
    {
        $request = GroupRequest::findOrFail($requestId);
        $group = $request->group;

        // Check if user is admin
        if (!$group->isAdmin(Auth::id())) {
            return back()->with('error', 'Only admin can reject requests.');
        }

        $request->update(['status' => 'rejected']);

        return back()->with('success', 'Request rejected.');
    }

    // Invite user to group (admin only)
    public function inviteUser(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        // Check if user is admin
        if (!$group->isAdmin(Auth::id())) {
            return back()->with('error', 'Only admin can invite users.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $userId = $request->user_id;

        // Check if already member
        if ($group->isMember($userId)) {
            return back()->with('error', 'User is already a member.');
        }

        DB::beginTransaction();

        try {
            // Add user directly (admin invite bypasses request)
            $group->members()->attach($userId, ['joined_at' => now()]);

            DB::commit();

            return back()->with('success', 'User invited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to invite user.');
        }
    }

    // Leave group
    public function leave($groupId)
    {
        $group = Group::findOrFail($groupId);
        $userId = Auth::id();

        // Admin cannot leave their own group
        if ($group->isAdmin($userId)) {
            return back()->with('error', 'Admin cannot leave the group. Transfer admin rights or delete the group.');
        }

        $group->members()->detach($userId);

        return redirect()->route('groups.index')->with('success', 'You have left the group.');
    }

    // Delete group (admin only)
    public function destroy($groupId)
    {
        $group = Group::findOrFail($groupId);

        // Check if user is admin
        if (!$group->isAdmin(Auth::id())) {
            return back()->with('error', 'Only admin can delete the group.');
        }

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }

    // Remove member (admin only)
    public function removeMember($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);

        // Check if user is admin
        if (!$group->isAdmin(Auth::id())) {
            return back()->with('error', 'Only admin can remove members.');
        }

        // Cannot remove admin
        if ($group->isAdmin($userId)) {
            return back()->with('error', 'Cannot remove admin from group.');
        }

        $group->members()->detach($userId);

        return back()->with('success', 'Member removed successfully.');
    }
}