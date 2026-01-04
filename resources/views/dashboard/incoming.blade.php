<x-app-layout>
    <div class="min-h-screen container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <x-peer-macthing-nav/>
            <div class="mx-auto mt-10">

                <h2 class="text-2xl font-bold mb-6">Incoming Connection Requests</h2>

                @if (session('success'))
                    <div class="p-3 mb-4 bg-green-100 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($requests->isEmpty())
                    <div class="p-4 bg-white shadow rounded text-gray-600">
                        No pending requests.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($requests as $req)
                            <div class="flex items-center justify-between p-4 bg-white rounded shadow">
                                <div>
                                    <p class="text-lg font-semibold">{{ $req->sender->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $req->sender->email }}</p>
                                </div>

                                <div class="flex space-x-2">

                                    <form action="{{ route('requests.accept', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition-colors duration-200 shadow-md">
                                            Accept
                                        </button>
                                    </form>

                                    <form action="{{ route('requests.reject', $req->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700 transition-colors duration-200 shadow-md">
                                            Reject
                                        </button>
                                    </form>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
