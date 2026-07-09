<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Added necessary security CSRF token for Laravel forms -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Smart Discussion Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { border-radius: 0px !important; font-family: 'Segoe UI', Inter, sans-serif; }
    </style>
</head>
<body class="bg-[#F9F9F9] flex items-center justify-center min-h-screen text-[#000000]">

    <div class="w-full max-w-md bg-white border border-[#E5E5E5] p-8 shadow-sm">
        <div class="mb-8 border-b border-[#000000] pb-4">
            <h1 class="text-xl font-bold uppercase tracking-wider">Smart Discussion Forum</h1>
            <p class="text-xs text-[#666666] mt-1">Web Client Workspace Portal</p>
        </div>

        {{-- Session and Validation Error Blocks --}}
        @if($errors->any())
            <div class="mb-4 border border-[#DC2626] p-3 bg-[#FEF2F2]">
                @foreach($errors->all() as $error)
                    <p class="text-xs text-[#DC2626]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 border border-[#16A34A] p-3 bg-[#F0FDF4]">
                @p class="text-xs text-[#16A34A]">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Workspace Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('email') border-[#DC2626] @enderror">
                @error('email')
                    <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Password</label>
                <input type="password" name="password" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-black">
                    <span class="text-xs text-[#666666]">Remember me</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-xs text-[#666666] hover:text-[#000000] underline">
                    Forgot password?
                </a>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full bg-white border-2 border-[#000000] py-2.5 text-sm font-bold uppercase tracking-wider text-[#000000] hover:bg-[#F5F5F5] active:bg-[#E5E5E5] transition-colors">
                    Access Workspace
                </button>
            </div>
        </form>

        <div class="mt-6 text-center space-y-3">
            <p class="text-xs text-[#666666]">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-[#000000] font-bold hover:underline">
                    Register
                </a>
            </p>
            <p class="text-[10px] text-[#666666]">
                <a href="#" class="hover:underline">Trouble logging in? Contact Administrator</a>
            </p>
        </div>
    </div>

</body>
</html>