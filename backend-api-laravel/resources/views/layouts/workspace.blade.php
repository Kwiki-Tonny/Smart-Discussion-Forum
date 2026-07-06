<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Discussion Forum')</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Custom CSS --}}
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>
<body class="bg-[#F9F9F9] text-[#000000] h-screen flex flex-col overflow-hidden">

    {{-- TOP BAR --}}
    <header class="h-10 bg-white border-b border-[#E5E5E5] flex items-center justify-between px-6 z-10 flex-shrink-0">
        <div class="text-sm font-bold tracking-tight">
            <a href="{{ route('dashboard') }}" class="text-[#000000] hover:opacity-60 transition-opacity">
                Smart Discussion Forum
            </a>
        </div>
        <div class="flex items-center space-x-6 text-sm">
            @auth
                <span class="text-xs font-medium text-[#666666]">
                    {{ Auth::user()->name }}
                </span>
                @if(Auth::user()->role === 'admin')
                    <span class="text-[9px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5">
                        Admin
                    </span>
                @elseif(Auth::user()->role === 'lecturer')
                    <span class="text-[9px] font-bold uppercase tracking-wider text-[#666666] border border-[#E5E5E5] px-2 py-0.5">
                        Lecturer
                    </span>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-[#666666] hover:text-[#000000] transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-xs text-[#666666] hover:text-[#000000] transition-colors">
                    Login
                </a>
                <a href="{{ route('register') }}" class="text-xs text-[#000000] border border-[#000000] px-3 py-1 hover:bg-[#F5F5F5] transition-colors">
                    Register
                </a>
            @endauth
        </div>
    </header>

    {{-- MAIN LAYOUT --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- SIDEBAR --}}
        <nav class="w-16 bg-[#FAFAFA] border-r border-[#E5E5E5] flex flex-col items-center py-6 justify-between flex-shrink-0">
            <div class="flex flex-col space-y-8 w-full items-center">
                <a href="{{ route('dashboard') }}" 
                   class="flex flex-col items-center text-[9px] font-bold text-[#000000] w-full py-2 bg-white border-y border-[#E5E5E5]">
                    <span class="text-[10px] mb-0.5">▣</span>
                    Groups
                </a>
                <a href="#" 
                   class="flex flex-col items-center text-[9px] font-medium text-[#666666] hover:text-[#000000] w-full py-2 transition-colors">
                    <span class="text-[10px] mb-0.5">▤</span>
                    Analytics
                </a>
                <a href="{{ route('recommendations.index') }}" 
                class="flex flex-col items-center text-[9px] font-medium text-[#666666] hover:text-[#000000] w-full py-2 transition-colors">
                    <span class="text-[10px] mb-0.5">◈</span>
                    Recs
                </a>
            </div>
        <a href="{{ route('profile') }}" 
        class="flex flex-col items-center text-[9px] font-medium text-[#666666] hover:text-[#000000] w-full py-2 transition-colors">
            <span class="text-[10px] mb-0.5">◉</span>
            Profile
        </a>
        </nav>

        {{-- CONTEXT PANEL --}}
        <aside class="w-72 bg-white border-r border-[#E5E5E5] flex flex-col flex-shrink-0 overflow-y-auto custom-scrollbar">
            @yield('context_panel')
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 bg-[#F9F9F9] overflow-y-auto custom-scrollbar relative">
            @yield('content')
        </main>

    </div>

    {{-- FOOTER --}}
    <footer class="h-6 bg-[#FAFAFA] border-t border-[#E5E5E5] flex items-center justify-between px-6 text-[11px] text-[#666666] font-medium flex-shrink-0">
        <div>
            <span class="inline-block w-1.5 h-1.5 bg-[#16A34A] rounded-full mr-2"></span>
            System Status: <span class="text-[#000000] font-bold">Online</span>
            <span class="mx-3">|</span>
            Database: <span class="text-[#000000] font-bold">Connected</span>
        </div>
        <div class="space-x-4">
            <a href="#" class="hover:underline">Privacy Policy</a>
            <a href="#" class="hover:underline">Terms of Service</a>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>