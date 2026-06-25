<nav class="bg-blue-800 text-white shadow-lg">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        
        {{-- LOGO --}}
        <a href="{{ url('/') }}" class="text-xl font-bold tracking-wide">
            🎓 Smart Discussion Forum
        </a>

        {{-- NAVIGATION LINKS --}}
        <div class="flex items-center space-x-6">
            @auth
                <a href="{{ route('dashboard') }}" 
                   class="hover:text-yellow-300 transition">
                    Dashboard
                </a>
                <a href="{{ route('groups.index') }}" 
                   class="hover:text-yellow-300 transition">
                    Groups
                </a>

                {{-- ADMIN ONLY --}}
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.analytics') }}" 
                       class="hover:text-yellow-300 transition">
                        Analytics
                    </a>
                @endif

                {{-- USER PROFILE --}}
                <span class="text-yellow-300 font-semibold">
                    {{ auth()->user()->name }}
                </span>

                {{-- LOGOUT --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded transition">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" 
                   class="hover:text-yellow-300 transition">
                    Login
                </a>
                <a href="{{ route('register') }}" 
                   class="bg-yellow-400 text-blue-900 px-3 py-1 rounded hover:bg-yellow-300 transition">
                    Register
                </a>
            @endauth
        </div>
    </div>
</nav>