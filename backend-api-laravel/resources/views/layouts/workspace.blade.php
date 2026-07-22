<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Discussion Forum')</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- Custom CSS --}}
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>
<body class="bg-[#F9F9F9] text-[#000000] h-screen flex flex-col overflow-hidden">
{{-- TOP BAR --}}
<header class="h-10 bg-[#F5F5F5] border-b border-[#E0E0E0] flex items-center justify-between px-6 z-10 flex-shrink-0">
    <div class="text-sm font-bold tracking-tight">
        <a href="{{ route('dashboard') }}" class="text-[#0A574F] hover:opacity-60 transition-opacity">
            Smart Discussion Forum
        </a>
    </div>
    <div class="flex items-center space-x-6 text-sm">
        @auth
            <span class="text-xs font-medium text-[#333333]">
                {{ Auth::user()->name }}
            </span>
            @if(Auth::user()->role === 'admin')
                <span class="text-[9px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-0.5 rounded-full">
                    Admin
                </span>
            @elseif(Auth::user()->role === 'lecturer')
                <span class="text-[9px] font-bold uppercase tracking-wider text-[#0A574F] border border-[#0A574F] px-2 py-0.5 rounded-full">
                    Lecturer
                </span>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-[#666666] hover:text-[#0A574F] transition-colors">
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="text-xs text-[#666666] hover:text-[#0A574F] transition-colors">
                Login
            </a>
            <a href="{{ route('register') }}" class="text-xs text-[#0A574F] border border-[#0A574F] px-3 py-1 rounded-lg hover:bg-[#0A574F] hover:text-white transition">
                Register
            </a>
        @endauth
    </div>
</header>

    {{-- MAIN LAYOUT --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- SIDEBAR --}}
        <nav class="w-20 bg-[#0A574F] border-r border-[#E5E5E5] flex flex-col items-center py-6 justify-between flex-shrink-0">
            {{-- Top Navigation Items --}}
            <div class="flex flex-col space-y-4 w-full items-center">

                @auth
                    @if(Auth::user()->role === 'student')
                        {{-- Student Menu --}}
                        <a href="{{ route('dashboard') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('dashboard') || request()->routeIs('groups.*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="users" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Groups</span>
                        </a>

                        <a href="{{ route('profile') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('profile') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="user" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Profile</span>
                        </a>

                        <a href="{{ route('recommendations.index') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('recommendations.*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="thumbs-up" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Recs</span>
                        </a>

                        <a href="{{ route('student.quizzes') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('student.quizzes*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="file-question" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Quizzes</span>
                        </a>

                    @elseif(Auth::user()->role === 'lecturer')
                        {{-- Lecturer Menu --}}
                        <a href="{{ route('lecturer.dashboard') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('lecturer.dashboard') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('lecturer.groups') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('lecturer.groups*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="folder" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>My Groups</span>
                        </a>

                        <a href="{{ route('groups.index') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('groups.*') && !request()->routeIs('lecturer.groups*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="globe" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>All Groups</span>
                        </a>

                        <a href="{{ route('lecturer.quizzes') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('lecturer.quizzes*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="file-question" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Quizzes</span>
                        </a>

                        <a href="{{ route('lecturer.grading') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('lecturer.grading') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="clipboard-check" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Grading</span>
                        </a>

                        <a href="{{ route('lecturer.profile') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('lecturer.profile') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="user" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Profile</span>
                        </a>

                    @elseif(Auth::user()->role === 'admin')
                        {{-- Admin Menu --}}
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.dashboard') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('admin.groups') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.groups*') || request()->routeIs('admin.group.statistics*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="bar-chart" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Analytics</span>
                        </a>

                        <a href="{{ route('admin.users') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.users*') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="users" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Users</span>
                        </a>

                        <a href="{{ route('admin.registrations') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.registrations') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="clipboard" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Registrations</span>
                        </a>

                        <a href="{{ route('admin.blacklist') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.blacklist') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="ban" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Blacklist</span>
                        </a>

                        <a href="{{ route('admin.configuration') }}" 
                           class="flex flex-col items-center text-center text-[10px] font-medium w-[70px] py-3 transition-all rounded-lg hover:shadow-sm
                                  {{ request()->routeIs('admin.configuration') ? 'bg-[#08443e] text-white font-bold shadow-sm' : 'text-white/70 hover:bg-[#08443e] hover:text-white' }}">
                            <i data-lucide="settings" class="w-5 h-5 mb-1" style="color:white;"></i>
                            <span>Config</span>
                        </a>
                    @endif
                @endauth

            </div>

            {{-- Bottom spacer with subtle border --}}
            <div class="flex flex-col space-y-6 w-full items-center border-t border-white/10 pt-4">
                <!-- reserved for future use -->
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

    {{-- Initialize Lucide Icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

    @stack('scripts')
</body>
</html>