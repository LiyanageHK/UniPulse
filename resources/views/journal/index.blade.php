<x-app-layout title="My Journal - UniPulse">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">📝 My Journal</h1>

        {{-- Success message --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
        @endif

        {{-- Access denied banner (based on week_end) --}}
        @if (isset($access) && !$access['allowed'])
            <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-4 rounded-lg mb-6">
                <p class="font-semibold">&#9888; Journal Submission Locked</p>
                @if ($access['duplicate'])
                    <p class="text-sm mt-1">
                        You already submitted a journal for
                        <strong>Week #{{ $access['current_week_index'] }}</strong>.
                        Your next entry will be available from
                        <strong>{{ $access['next_allowed'] }}</strong>.
                    </p>
                @else
                    <p class="text-sm mt-1">
                        Your current week ends on <strong>{{ $access['week_end'] }}</strong>.
                        You can write your next journal entry in
                        <strong>{{ $access['days_remaining'] }} day(s)</strong>.
                    </p>
                @endif
            </div>
        @endif

        {{-- Write / Update Today Entry (only if allowed) --}}
        @if (isset($access) && $access['allowed'])
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">
                    {{ $todayEntry ? "Update Today's Entry" : "Write Today's Entry" }}
                    @if ($access['current_week_index'])
                        <span class="text-sm font-normal text-gray-400 ml-2">(Week #{{ $access['current_week_index'] }})</span>
                    @endif
                </h2>
                <p class="text-sm text-gray-500 mb-4">{{ now()->format('l, F j, Y') }}</p>

                <form action="{{ route('journal.store') }}" method="POST">
                    @csrf
                    <textarea id="journalContent" name="content" rows="6"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 resize-y"
                        placeholder="How are you feeling today? Write freely about your thoughts, experiences, and emotions..."
                        required minlength="10" maxlength="5000">{{ old('content', $todayEntry?->content) }}</textarea>

                    @error('content')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                            {{ $todayEntry ? 'Update Entry' : 'Save Entry' }}
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Previous Entries --}}
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Previous Entries</h2>

        @forelse ($journals as $journal)
            <div class="bg-white rounded-xl shadow p-5 mb-4 hover:shadow-md transition">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-purple-600">
                        {{ $journal->entry_date->format('l, M d, Y') }}
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('journal.show', $journal->id) }}"
                            class="text-sm text-blue-600 hover:underline">View</a>
                        <form action="{{ route('journal.destroy', $journal->id) }}" method="POST"
                            onsubmit="return confirm('Delete this entry?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                <p class="text-gray-600 text-sm line-clamp-3">{{ $journal->content }}</p>
            </div>
        @empty
            <div class="bg-gray-50 rounded-xl p-8 text-center text-gray-400">
                <p class="text-lg">No journal entries yet.</p>
                <p class="text-sm mt-1">Start writing above to track your feelings over time.</p>
            </div>
        @endforelse

        <div class="mt-6">{{ $journals->links() }}</div>
    </div>
</x-app-layout>
