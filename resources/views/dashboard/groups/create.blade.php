{{-- resources/views/dashboard/groups/create.blade.php --}}
<x-app-layout title="Create Group - UniPulse">
    <div class="min-h-screen container mx-auto px-4 py-10">
        <div class="max-w-7xl mx-auto">
            <div class=" mx-auto px-4 py-6">
                <div class="flex items-center mb-6">
                    <a href="{{ route('groups.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">Create New Group</h1>
                </div>

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-lg shadow-md p-6">
                    <form method="POST" action="{{ route('groups.store') }}">
                        @csrf

                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Group Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter group name">
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Category *</label>
                            <select name="category" required
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select a category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}"
                                        {{ old('category') == $category ? 'selected' : '' }}>
                                        {{ $category }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">Description</label>
                            <textarea name="description" rows="4"
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Describe your group...">{{ old('description') }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Optional: Tell people what this group is about</p>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-blue-900 mb-2">Group Creation Info:</h3>
                            <ul class="text-sm text-blue-800 space-y-1">
                                <li>• You will be the admin of this group</li>
                                <li>• You can invite members or accept join requests</li>
                                <li>• Members can send messages in the group chat</li>
                            </ul>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('groups.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                                Cancel
                            </a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                Create Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
