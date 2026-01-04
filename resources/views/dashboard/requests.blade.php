<x-app-layout>

<div class="px-6 py-6">

    <h1 class="text-xl font-bold mb-4">Peer Requests</h1>

    @foreach ($requests as $req)
        <div class="p-4 bg-white shadow border rounded-lg mb-3 flex justify-between items-center">
            <span class="font-semibold">{{ $req->sender->name }}</span>

            <div class="flex space-x-2">
                <form method="POST" action="{{ route('peer.accept', $req->id) }}">
                    @csrf
                    <button class="px-3 py-1 bg-green-500 text-white rounded">Accept</button>
                </form>

                <form method="POST" action="{{ route('peer.reject', $req->id) }}">
                    @csrf
                    <button class="px-3 py-1 bg-red-500 text-white rounded">Reject</button>
                </form>
            </div>
        </div>
    @endforeach

</div>

</x-app-layout>
