<x-app-layout title="AI Peer Matching - UniPulse">
    <x-peer-macthing-nav />

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-10">

            {{-- Header --}}
            <div class="flex justify-between items-start mb-8 mt-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">AI Peer Matching</h1>
                    <p class="text-gray-500 mt-1 text-sm">Find your ideal group based on personality, faculty, and interests</p>
                </div>
                <a href="{{ route('groups.index') }}"
                    class="inline-flex items-center gap-2 bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition">
                    &larr; Back to Groups
                </a>
            </div>

            {{-- Error alert --}}
            @if ($errors->has('generate'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first('generate') }}</span>
                </div>
            @endif

            {{-- Form --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8">
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-xl font-bold text-gray-900">Find My Best Match Group</h2>
                </div>
                <form action="{{ route('peer-matching.find-my-group') }}" method="POST" id="findForm">
                    @csrf
                    <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">

                        {{-- Purpose --}}
                        <div style="flex:1; min-width:160px;">
                            <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:6px;">Purpose</label>
                            <select name="purpose"
                                style="width:100%; height:48px; border:1px solid #d1d5db; border-radius:12px; padding:0 16px; background:#fff; color:#1f2937; font-size:0.95rem; outline:none; appearance:auto;">
                                <option value="default"     {{ old('purpose', $purpose) === 'default'     ? 'selected' : '' }}>Default</option>
                                <option value="academic"    {{ old('purpose', $purpose) === 'academic'    ? 'selected' : '' }}>Academic</option>
                                <option value="hobby"       {{ old('purpose', $purpose) === 'hobby'       ? 'selected' : '' }}>Hobby</option>
                                <option value="personality" {{ old('purpose', $purpose) === 'personality' ? 'selected' : '' }}>Personality</option>
                            </select>
                        </div>

                        {{-- Group Size --}}
                        <div style="width:160px;">
                            <label style="display:block; font-size:0.875rem; font-weight:600; color:#374151; margin-bottom:6px;">Group Size</label>
                            <input type="number" name="group_size"
                                value="{{ old('group_size', $groupSize ?? 5) }}"
                                min="2" max="20" placeholder="e.g. 5"
                                style="width:100%; height:48px; border:1px solid #d1d5db; border-radius:12px; padding:0 16px; color:#1f2937; font-size:0.95rem; outline:none; box-sizing:border-box;">
                        </div>

                        {{-- Button --}}
                        <div>
                            <button type="submit" id="findBtn"
                                style="height:48px; padding:0 32px; background:#2563eb; color:#ffffff; font-weight:700; font-size:0.95rem; border:none; border-radius:12px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; white-space:nowrap; box-shadow:0 2px 8px rgba(37,99,235,0.3);">
                                <svg style="width:20px;height:20px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span id="btnText">Find My Group</span>
                            </button>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Results --}}
            @if (isset($matches) && $matches->count() > 0)
                @php
                    $purposeLabel = ['default' => 'General', 'academic' => 'Academic', 'hobby' => 'Hobby', 'personality' => 'Personality'][$purpose] ?? ucfirst($purpose ?? '');
                    $purposeEmoji = ['default' => '🌐', 'academic' => '📚', 'hobby' => '🎨', 'personality' => '🧠'][$purpose] ?? '👥';
                    $avgScore     = round($matches->avg('match_score'));
                    $requestStatuses  = $requestStatuses  ?? collect();
                    $sentRequestIds   = $sentRequestIds   ?? collect();
                    $incomingStatuses = $incomingStatuses ?? collect();
                @endphp

                {{-- Summary banner --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 mb-6 text-white shadow-lg">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Your Best Match Group</p>
                            <h3 class="text-2xl font-bold">{{ $purposeEmoji }} {{ $purposeLabel }} Group</h3>
                            <p class="text-blue-200 text-sm mt-1">{{ $matches->count() }} students &bull; Average compatibility {{ $avgScore }}%</p>
                        </div>
                        <div class="text-center bg-white/20 backdrop-blur rounded-xl px-7 py-3">
                            <p class="text-4xl font-extrabold leading-none">{{ $avgScore }}%</p>
                            <p class="text-blue-200 text-xs mt-1 font-medium">Avg Match</p>
                        </div>
                    </div>
                </div>

                {{-- Match cards --}}
                <div class="space-y-4">
                    @foreach ($matches as $index => $match)
                        @php
                            $user  = $match['user']        ?? null;
                            $score = $match['match_score'] ?? 0;
                            $rank  = $match['rank']        ?? ($index + 1);

                            if      ($score >= 80) { $barColor = 'bg-green-500'; $badge = 'bg-green-100 text-green-700';    $qlabel = 'Excellent'; }
                            elseif  ($score >= 60) { $barColor = 'bg-blue-500';  $badge = 'bg-blue-100 text-blue-700';      $qlabel = 'Great';     }
                            elseif  ($score >= 40) { $barColor = 'bg-yellow-500';$badge = 'bg-yellow-100 text-yellow-700';  $qlabel = 'Good';      }
                            else                   { $barColor = 'bg-orange-500';$badge = 'bg-orange-100 text-orange-700';  $qlabel = 'Fair';      }

                            $initials  = $user ? strtoupper(substr($user->name, 0, 2)) : '??';
                            $gradients = ['from-blue-400 to-blue-600','from-purple-400 to-purple-600','from-green-400 to-green-600',
                                          'from-pink-400 to-pink-600','from-orange-400 to-orange-600','from-teal-400 to-teal-600'];
                            $grad = $gradients[$index % count($gradients)];

                            $mid        = $match['user_id'] ?? null;
                            $sentSt     = $requestStatuses->get($mid);
                            $recvSt     = $incomingStatuses->get($mid);
                            if      ($sentSt === 'accepted' || $recvSt === 'accepted') { $reqState = 'accepted'; }
                            elseif  ($sentSt === 'pending')                            { $reqState = 'pending';  }
                            elseif  ($recvSt === 'pending')                            { $reqState = 'incoming'; }
                            else                                                       { $reqState = 'none';     }
                        @endphp

                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all p-5">
                            <div class="flex items-start gap-4">

                                {{-- Rank --}}
                                <div class="flex-shrink-0 w-8 pt-1 text-center">
                                    <span class="text-sm font-bold text-gray-400">#{{ $rank }}</span>
                                </div>

                                {{-- Avatar --}}
                                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br {{ $grad }}
                                            flex items-center justify-center text-white font-extrabold text-base shadow-sm select-none">
                                    {{ $initials }}
                                </div>

                                {{-- Main content --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Name + badge --}}
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <p class="font-bold text-gray-900 text-base leading-tight">{{ $user->name ?? 'Unknown Student' }}</p>
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $badge }}">{{ $qlabel }}</span>
                                    </div>

                                    {{-- Tags --}}
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @if (!empty($match['faculty']))
                                            <span class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full">🏛 {{ $match['faculty'] }}</span>
                                        @endif
                                        @if (!empty($match['al_stream']))
                                            <span class="bg-gray-100 text-gray-600 text-xs px-2.5 py-1 rounded-full">📖 {{ $match['al_stream'] }}</span>
                                        @endif
                                        @if (!empty($match['learning_style']))
                                            <span class="bg-indigo-50 text-indigo-600 text-xs px-2.5 py-1 rounded-full">💡 {{ $match['learning_style'] }}</span>
                                        @endif
                                        @if (!empty($match['stress_level']))
                                            <span class="bg-rose-50 text-rose-600 text-xs px-2.5 py-1 rounded-full">😓 {{ $match['stress_level'] }} Stress</span>
                                        @endif
                                        @if (!empty($match['social_setting']))
                                            <span class="bg-teal-50 text-teal-600 text-xs px-2.5 py-1 rounded-full">👥 {{ $match['social_setting'] }}</span>
                                        @endif
                                    </div>

                                    {{-- Compatibility bar --}}
                                    <div>
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-xs text-gray-400 font-medium">Compatibility</span>
                                            <span class="text-sm font-bold text-gray-800">{{ $score }}%</span>
                                        </div>
                                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $score }}%"></div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Action buttons --}}
                                @if ($user)
                                    <div class="flex-shrink-0 flex flex-col gap-2 w-[120px]">

                                        @if ($reqState === 'accepted')
                                            {{-- Connected: static badge --}}
                                            <span style="display:block;width:100%;text-align:center;background:#dcfce7;border:1px solid #86efac;color:#166534;font-size:0.75rem;font-weight:700;padding:8px 12px;border-radius:10px;">
                                                ✅ Connected
                                            </span>
                                        @elseif ($reqState === 'pending')
                                            {{-- Pending: clicking it cancels the request --}}
                                            <form action="{{ route('peer.cancel', $sentRequestIds->get($mid)) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Click to cancel request"
                                                    style="width:100%;background:#fef9c3;border:1px solid #fde047;color:#854d0e;font-size:0.75rem;font-weight:700;padding:8px 12px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;">
                                                    ⏳ Pending
                                                </button>
                                            </form>
                                        @elseif ($reqState === 'incoming')
                                            {{-- Incoming: static badge --}}
                                            <span style="display:block;width:100%;text-align:center;background:#f3e8ff;border:1px solid #d8b4fe;color:#6b21a8;font-size:0.75rem;font-weight:700;padding:8px 12px;border-radius:10px;">
                                                📬 Incoming
                                            </span>
                                        @else
                                            {{-- None: connect button --}}
                                            <form action="{{ route('peer.send', $user->id) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                    style="width:100%;background:#4f46e5;border:1px solid #4338ca;color:#ffffff;font-size:0.75rem;font-weight:700;padding:8px 12px;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:4px;">
                                                    <svg style="width:13px;height:13px;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                                    </svg>
                                                    Connect
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('profile.view', $user->id) }}"
                                            class="w-full flex items-center justify-center gap-1 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 text-xs font-bold px-3 py-2 rounded-xl transition">
                                            View Profile
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>

                                    </div>
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>

            @elseif ($purpose)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-14 text-center">
                    <div class="text-6xl mb-4">🔍</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">No matches found</h3>
                    <p class="text-gray-500 text-sm">Try a different purpose or ensure your onboarding profile is complete.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-14 text-center">
                    <div class="text-6xl mb-4">🤝</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Ready to find your group?</h3>
                    <p class="text-gray-500 text-sm max-w-sm mx-auto">
                        Pick a purpose and group size above, then click <strong>Find My Group</strong> — the AI will
                        match you with the most compatible students.
                    </p>
                </div>
            @endif

        </div>
    </div>

    <script>
        // Detect browser refresh (F5 / Ctrl+R) and clear results
        const navEntry = performance.getEntriesByType('navigation')[0];
        if (navEntry && navEntry.type === 'reload') {
            const clearUrl = '{{ route("peer-matching.index") }}?clear=1';
            if (!window.location.href.includes('clear=1')) {
                window.location.replace(clearUrl);
            }
        }

        document.getElementById('findForm').addEventListener('submit', function () {
            const btn  = document.getElementById('findBtn');
            const text = document.getElementById('btnText');
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
            text.textContent = 'Searching…';
        });
    </script>
</x-app-layout>
