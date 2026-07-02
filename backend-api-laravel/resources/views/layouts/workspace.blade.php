<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { border-radius: 0px !important; font-family: 'Segoe UI', Inter, sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E5E5E5; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #666666; }
    </style>
</head>
<body class="bg-[#F9F9F9] text-[#000000] h-screen flex flex-col overflow-hidden">

    <header class="h-10 bg-white border-b border-[#E5E5E5] flex items-center justify-between px-6 z-10 flex-shrink-0">
        <div class="text-sm font-bold tracking-tight">
            Smart Discussion Forum-Web Workspace
        </div>
        <div class="flex items-center space-x-4 text-sm">
            <button class="hover:opacity-60">🔍</button>
            <button class="hover:opacity-60">🔔</button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        
        <nav class="w-16 bg-[#FAFAFA] border-r border-[#E5E5E5] flex flex-col items-center py-4 justify-between flex-shrink-0">
            <div class="flex flex-col space-y-6 w-full items-center">
                <a href="#" class="flex flex-col items-center text-center text-[10px] font-bold text-[#000000] w-full py-2 bg-white border-y border-[#E5E5E5]">
                    <span class="text-xl mb-0.5">👥</span>Groups
                </a>
                <a href="#" class="flex flex-col items-center text-center text-[10px] font-medium text-[#666666] hover:text-[#000000] w-full py-2">
                    <span class="text-xl mb-0.5">📊</span>Analytics
                </a>
                <a href="#" class="flex flex-col items-center text-center text-[10px] font-medium text-[#666666] hover:text-[#000000] w-full py-2">
                    <span class="text-xl mb-0.5">💡</span>Recs
                </a>
            </div>
            <a href="#" class="flex flex-col items-center text-center text-[10px] font-medium text-[#666666] hover:text-[#000000] w-full py-2">
                <span class="text-xl mb-0.5">👤</span>Profile
            </a>
        </nav>

        <aside class="w-72 bg-white border-r border-[#E5E5E5] flex flex-col flex-shrink-0 overflow-y-auto custom-scrollbar">
            @yield('context_panel')
        </aside>

        <main class="flex-1 bg-[#F9F9F9] overflow-y-auto custom-scrollbar relative">
            @yield('content')
        </main>

    </div>

    <footer class="h-6 bg-[#FAFAFA] border-t border-[#E5E5E5] flex items-center justify-between px-6 text-[11px] text-[#666666] font-medium flex-shrink-0">
        <div>System Status: <span class="text-[#000000] font-bold">Online</span> | Database: <span class="text-[#000000] font-bold">Connected</span></div>
        <div class="space-x-4">
            <a href="#" class="hover:underline">Privacy Policy</a>
            <a href="#" class="hover:underline">Terms of Service</a>
        </div>
    </footer>

</body>
</html>