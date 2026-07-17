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
        <nav class="w-20 bg-[#FAFAFA] border-r border-[#E5E5E5] flex flex-col items-center py-6 justify-between flex-shrink-0">
            {{-- Top Navigation Items --}}
            <div class="flex flex-col space-y-6 w-full items-center">

                @auth
                    @if(Auth::user()->role === 'student')
                        {{-- Student Menu --}}
                        <a href="{{ route('dashboard') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('dashboard') || request()->routeIs('groups.*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▣</span>
                            <span>Groups</span>
                        </a>

                        <a href="{{ route('profile') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('profile') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">◉</span>
                            <span>Profile</span>
                        </a>

                        <a href="{{ route('recommendations.index') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('recommendations.*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">◈</span>
                            <span>Recs</span>
                        </a>

                        <a href="{{ route('student.quizzes') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('student.quizzes*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">📝</span>
                            <span>Quizzes</span>
                        </a>

                    @elseif(Auth::user()->role === 'lecturer')
                        {{-- Lecturer Menu --}}
                        <a href="{{ route('lecturer.dashboard') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('lecturer.dashboard') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▣</span>
                            <span>Dashboard</span>
                        </a>

                        {{-- My Groups (management) --}}
                        <a href="{{ route('lecturer.groups') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('lecturer.groups*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▤</span>
                            <span>My Groups</span>
                        </a>

                        {{-- All Groups (participation) --}}
                        <a href="{{ route('groups.index') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('groups.*') && !request()->routeIs('lecturer.groups*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">🌐</span>
                            <span>All Groups</span>
                        </a>

                        <a href="{{ route('lecturer.quizzes') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('lecturer.quizzes*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">📝</span>
                            <span>Quizzes</span>
                        </a>

                        <a href="{{ route('lecturer.grading') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('lecturer.grading') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">📊</span>
                            <span>Grading</span>
                        </a>

                        <a href="{{ route('lecturer.profile') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('lecturer.profile') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">◉</span>
                            <span>Profile</span>
                        </a>

                    @elseif(Auth::user()->role === 'admin')
                        {{-- Admin Menu --}}
                        <a href="{{ route('admin.dashboard') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.dashboard') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▣</span>
                            <span>Dashboard</span>
                        </a>

                        {{-- ✅ Analytics (Groups List) --}}
                        <a href="{{ route('admin.groups') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.groups*') || request()->routeIs('admin.group.statistics*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▤</span>
                            <span>Analytics</span>
                        </a>

                        <a href="{{ route('admin.users') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.users*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">👥</span>
                            <span>Users</span>
                        </a>

                        <a href="{{ route('admin.registrations') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.registrations') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">📋</span>
                            <span>Registrations</span>
                        </a>

                        <a href="{{ route('admin.blacklist') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.blacklist') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">🚫</span>
                            <span>Blacklist</span>
                        </a>

                        <a href="{{ route('admin.configuration') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('admin.configuration') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">⚙</span>
                            <span>Config</span>
                        </a>
                    @endif
                @endauth

            </div>

            {{-- Bottom – Settings (optional) --}}
            <div class="flex flex-col space-y-6 w-full items-center border-t border-[#E5E5E5] pt-4">
                <!-- Removed dummy settings button -->
            </div>
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