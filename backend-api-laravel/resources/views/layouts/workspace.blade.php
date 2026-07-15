

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://unpkg.com/lucide@latest"></script>
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
<body class="bg-[#F9F9F9] text-[#000000] min-h-screen flex flex-col">

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
            <i data-lucide="users" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Groups</span>
        </a>

        <a href="{{ route('profile') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('profile') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="user" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Profile</span>
        </a>

        <a href="{{ route('recommendations.index') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('recommendations.*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="sparkles" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Recs</span>
        </a>

        <a href="{{ route('student.quizzes') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('student.quizzes*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="file-question" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Quizzes</span>
        </a>

    @elseif(Auth::user()->role === 'lecturer')
        {{-- Lecturer Menu --}}
        <a href="{{ route('lecturer.dashboard') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('lecturer.dashboard') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="layout-dashboard" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Dashboard</span>
        </a>

        {{-- Groups link – same as student but lecturers can access --}}
        <a href="{{ route('groups.index') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('groups.*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="folder-git" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Groups</span>
        </a>

        <a href="{{ route('lecturer.quizzes') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('lecturer.quizzes*') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="file-check" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Quizzes</span>
        </a>

        <a href="{{ route('lecturer.grading') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('lecturer.grading') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="bar-chart-3" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Grading</span>
        </a>

        <a href="{{ route('lecturer.profile') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors
                {{ request()->routeIs('lecturer.profile') ? 'bg-white border-y border-[#E5E5E5] text-[#000000] font-bold' : 'text-[#666666] hover:text-[#000000] hover:bg-[#F0F0F0]' }}">
            <i data-lucide="user" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Profile</span>
        </a>

    @elseif(Auth::user()->role === 'admin')
        {{-- Admin Menu --}}
        <a href="{{ route('admin.dashboard') }}" 
        class="flex flex-col items-center text-center text-[10px] font-medium w-full py-3 transition-colors">
            <i data-lucide="shield-alert" class="w-5 h-5 mb-1 text-slate-800"></i>
            <span>Admin</span>
        </a>
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

       {{-- MAIN CONTENT WITH GRADIENT DEPTH --}}
<main class="flex-1 bg-slate-50/70 overflow-y-auto custom-scrollbar relative min-h-screen">
    
    {{-- Decorative Ambient Glows (Adds subtle professional depth) --}}
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-b from-indigo-50/40 via-transparent to-transparent rounded-full blur-3xl pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-1/4 w-[600px] h-[600px] bg-gradient-to-t from-emerald-50/30 via-transparent to-transparent rounded-full blur-3xl pointer-events-none z-0"></div>

    {{-- Content Wrapper (Keeps your dashboard content on top of the background glows) --}}
    <div class="relative z-10">
        @yield('content')
    </div>
    
</main>
    

    @stack('scripts')

    <script>
  lucide.createIcons();
</script>
</body>
</html>

