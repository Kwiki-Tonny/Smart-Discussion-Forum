<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Smart Discussion Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Keep all other corners sharp, but we'll override inputs & button with rounded-sm via classes */
        * { border-radius: 0px !important; }
        /* Override for specific elements that need rounded corners */
        .rounded-input, .rounded-btn {
            border-radius: 4px !important; /* minimal border radius */
        }
        /* Ensure focus ring works with the global reset */
        .focus-ring:focus {
            outline: none !important;
            border-color: #000000 !important;
            box-shadow: 0 0 0 1px #000000 !important;
        }
    </style>
</head>
<body class="bg-[#F9F9F9] flex items-center justify-center min-h-screen text-[#000000]">

    <div class="w-full max-w-md bg-white border border-[#E5E5E5] p-8 shadow-sm">

        {{-- Header --}}
        <div class="mb-8 border-b border-[#000000] pb-4">
            <h1 class="text-xl font-bold uppercase tracking-wider">Smart Discussion Forum</h1>
            <p class="text-xs text-[#666666] mt-1">Sign in to access your workspace</p>
        </div>

        {{-- Error / Status Messages --}}
        @if($errors->any())
            <div class="mb-4 border border-[#DC2626] p-3 bg-[#FEF2F2]">
                @foreach($errors->all() as $error)
                    <p class="text-xs text-[#DC2626]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 border border-[#16A34A] p-3 bg-[#F0FDF4]">
                <p class="text-xs text-[#16A34A]">{{ session('status') }}</p>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
            @csrf

            {{-- Email Field --}}
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Email Address</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#666666]">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                           class="rounded-input w-full bg-white border border-[#E5E5E5] pl-9 pr-3 py-2 text-sm focus-ring @error('email') border-[#DC2626] @enderror">
                </div>
                @error('email')
                    <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Field --}}
            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#666666]">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" name="password" required 
                           class="rounded-input w-full bg-white border border-[#E5E5E5] pl-9 pr-3 py-2 text-sm focus-ring">
                </div>
            </div>

            {{-- Remember & Forgot --}}
            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-black">
                    <span class="text-xs text-[#666666]">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-xs text-[#666666] hover:text-[#000000] underline">
                    Forgot password?
                </a>
            </div>

            {{-- Submit Button with new background --}}
            <div class="pt-2">
                <button type="submit" 
                        class="rounded-btn w-full bg-[#0D6E64] border-2 border-[#0D6E64] py-2.5 text-sm font-bold uppercase tracking-wider text-white hover:bg-[#0A5A52] hover:border-[#0A5A52] transition-colors">
                    Access Workspace
                </button>
            </div>
        </form>

        {{-- Footer Links --}}
        <div class="mt-6 text-center space-y-3">
            <p class="text-xs text-[#666666]">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-[#0D6E64] font-bold hover:underline">
                    Register
                </a>
            </p>
            <p class="text-[10px] text-[#666666]">
                <a href="#" class="hover:underline">Trouble logging in? Contact Administrator</a>
            </p>
        </div>

<!--         {{-- System Status Footer --}}
        <div class="mt-6 pt-4 border-t border-[#E5E5E5] flex items-center justify-between text-[11px] text-[#666666] font-medium">
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
        </div> -->
    </div>

    {{-- Initialize Lucide Icons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>

</body>
</html>