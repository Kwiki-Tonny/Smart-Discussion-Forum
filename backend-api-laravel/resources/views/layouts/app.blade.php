<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-gray-900 m-0 p-0 flex flex-col min-h-screen">

    <!-- Main Content wrapper (This will expand to push the footer down naturally) -->
    <div class="flex-grow">
        @yield('content')
    </div>

    <!-- The Global Footer (Status Bar & Policy links are now pinned down here) -->
    <footer class="bg-white border-t border-gray-200 py-4 px-6 mt-12 w-full">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center text-xs text-gray-500 space-y-2 md:space-y-0">
            <!-- System / Database Status -->
            <div class="flex items-center space-x-4">
                <span class="flex items-center">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block mr-2 animate-pulse"></span>
                    System Status: <strong class="text-emerald-950 font-semibold ml-1">Online</strong>
                </span>
                <span class="text-gray-300">|</span>
                <span>
                    Database: <strong class="text-emerald-950 font-semibold ml-1">Connected</strong>
                </span>
            </div>
            
            <!-- Policy Links -->
            <div class="flex space-x-4">
                <a href="#" class="hover:text-emerald-600 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-emerald-600 transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons globally (Only need it once right before body ends!) -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>