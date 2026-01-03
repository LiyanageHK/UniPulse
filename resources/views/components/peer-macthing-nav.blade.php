<br><br>
<div class="container mx-auto px-4 pt-10">
    <div class="max-w-6xl mx-auto text-center">
        <div class="inline-flex border-2 border-blue-600 rounded-lg overflow-hidden shadow-sm">
            <a href="{{ route('peer-matchings') }}"
                class="px-6 py-3 font-semibold transition-colors border-r-2 border-blue-600 {{ request()->routeIs('peer-matchings') ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                Suggestions
            </a>

            <a href="{{ route('myConnections') }}"
                class="px-6 py-3 font-semibold transition-colors border-r-2 border-blue-600 {{ request()->routeIs('myConnections') ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                Connections
            </a>

            <a href="{{ route('chat.view') }}"
                class="px-6 py-3 font-semibold transition-colors border-r-2 border-blue-600 {{ request()->routeIs('chat.view') ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                Chat
            </a>

            <a href="{{ route('requests.incoming') }}"
                class="px-6 py-3 font-semibold transition-colors border-r-2 border-blue-600 {{ request()->routeIs('requests.incoming') ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                Requests
            </a>

            <a href="{{ route('groups.index') }}"
                class="px-6 py-3 font-semibold transition-colors {{ request()->routeIs('groups.index') ? 'bg-blue-600 text-white' : 'bg-white text-blue-600 hover:bg-blue-50' }}">
                Groups
            </a>
        </div>
    </div>
</div>
