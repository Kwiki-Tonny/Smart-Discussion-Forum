<footer class="bg-blue-800 text-white mt-auto py-6">
    <div class="container mx-auto px-4">
        
        {{-- TOP SECTION --}}
        <div class="flex justify-between items-center mb-4">
            
            {{-- LOGO --}}
            <div>
                <h2 class="text-xl font-bold">🎓 Smart Discussion Forum</h2>
                <p class="text-blue-300 text-sm">
                    A collaborative academic platform
                </p>
            </div>

            {{-- LINKS --}}
            <div class="flex space-x-6">
                <a href="{{ url('/') }}" 
                   class="hover:text-yellow-300 transition text-sm">
                    Home
                </a>
                <a href="{{ route('groups.index') }}" 
                   class="hover:text-yellow-300 transition text-sm">
                    Groups
                </a>
                <a href="{{ route('login') }}" 
                   class="hover:text-yellow-300 transition text-sm">
                    Login
                </a>
            </div>
        </div>

        {{-- BOTTOM SECTION --}}
        <div class="border-t border-blue-600 pt-4 text-center text-blue-300 text-sm">
            <p>
                &copy; {{ date('Y') }} Smart Discussion Forum. 
                All rights reserved.
            </p>
            <p class="mt-1">
                Built with ❤️ by TRINA Development Team
            </p>
        </div>

    </div>
</footer>