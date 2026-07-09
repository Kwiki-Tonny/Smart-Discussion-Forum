<<<<<<< HEAD
@extends('layouts.app')

@section('content')
<div class="workspace-split">
    
    <nav class="app-sidebar">
        <div class="app-icon" title="Groups">💬</div>
        <div class="app-icon" title="Profile">👤</div>
        <div class="app-icon" title="Performance">📊</div>
        <div class="app-icon" title="Recommendations">💡</div>
    </nav>

    <aside class="navigation-container">
        
        <div id="groups-view" class="nav-card">
            <div class="nav-header">Your Groups</div>
            <ul class="nav-list">
                <li onclick="window.openGroup('Web Development')">Web Development</li>
                <li onclick="window.openGroup('Software Engineering')">Software Engineering</li>
                <li onclick="window.openGroup('Numerical Analysis')">Numerical Analysis</li>
                <li onclick="window.openGroup('System Admin')">System Admin</li>
            </ul>
        </div>

        <div id="topics-view" class="nav-card card-hidden">
            <div class="nav-header-topics">
                <button class="back-btn" onclick="window.goBack()">◄ Back</button>
                <span id="current-group-title" style="color: #ccc; font-weight: bold;">Group Name</span>
            </div>
            <ul class="nav-list">
                <li>General Discussion</li>
                <li>Exam Prep Resources</li>
                <li id="dynamic-assignment">Assignments</li>
            </ul>
        </div>

    </aside>

    <main class="main-content">
        <div class="placeholder">Select a topic to view messages</div>
    </main>

</div>

<footer class="status-bar">
    <div class="status-online">● Status: Online Web Workspace</div>
    <div class="status-db">● Connected to Laravel Backend</div>
</footer>
@endsection
=======
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

                        {{-- Groups link – same as student but lecturers can access --}}
                        <a href="{{ route('groups.index') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                                {{ request()->routeIs('groups.*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
                            <span class="text-xl mb-1">▤</span>
                            <span>Groups</span>
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
                        {{-- Admin Menu (similar to lecturer but with extra) --}}
                        <a href="{{ route('admin.dashboard') }}" 
                        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors">
                            <span class="text-xl mb-1">▣</span>
                            <span>Admin</span>
                        </a>
                        {{-- Add other admin links as needed --}}
                    @endif
                @endauth

            </div>

            {{-- Bottom – Settings/Logout (if needed) --}}
            <div class="flex flex-col space-y-6 w-full items-center border-t border-[#E5E5E5] pt-4">
                <a href="#" 
                class="flex flex-col items-center text-center text-[10px] font-medium w-full py-2 transition-colors text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]">
                    <span class="text-base mb-1">⚙</span>
                    <span>Settings</span>
                </a>
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
>>>>>>> origin/main
