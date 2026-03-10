<x-app-layout title="Journal Entry - UniPulse">
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('journal.index') }}" class="text-purple-600 hover:underline text-sm mb-4 inline-block">
            ← Back to Journal
        </a>

        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $journal->entry_date->format('l, F j, Y') }}
                </h1>
                <span class="text-sm text-gray-400">
                    {{ $journal->created_at->diffForHumans() }}
                </span>
            </div>

            <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $journal->content }}</div>
        </div>
    </div>
</x-app-layout>
