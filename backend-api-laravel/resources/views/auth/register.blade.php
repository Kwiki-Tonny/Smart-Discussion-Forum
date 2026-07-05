<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Smart Discussion Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { border-radius: 0px !important; font-family: 'Segoe UI', Inter, sans-serif; }
    </style>
</head>
<body class="bg-[#F9F9F9] flex items-center justify-center min-h-screen text-[#000000]">

    <div class="w-full max-w-md bg-white border border-[#E5E5E5] p-8 shadow-sm">
        <div class="mb-8 border-b border-[#000000] pb-4">
            <h1 class="text-xl font-bold uppercase tracking-wider">Smart Discussion Forum</h1>
            <p class="text-xs text-[#666666] mt-1">Create your account</p>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="mb-4 border border-[#DC2626] p-3 bg-[#FEF2F2]">
                @foreach($errors->all() as $error)
                    <p class="text-xs text-[#DC2626]">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.submit') }}" class="space-y-5">
            @csrf

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('name') border-[#DC2626] @enderror">
                @error('name')
                    <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors @error('email') border-[#DC2626] @enderror">
                @error('email')
                    <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Password</label>
                <input type="password" name="password" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
                <p class="text-[10px] text-[#666666]">Minimum 8 characters</p>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold uppercase tracking-wide text-[#000000]">Confirm Password</label>
                <input type="password" name="password_confirmation" required 
                       class="w-full bg-white border border-[#E5E5E5] px-3 py-2 text-sm focus:outline-none focus:border-[#000000] transition-colors">
            </div>

            <div class="flex items-start space-x-3 pt-1">
                <input type="checkbox" name="terms" required 
                       class="mt-0.5 accent-black h-4 w-4 border border-[#E5E5E5]">
                <label class="text-xs text-[#666666] leading-relaxed">
                    I agree to the 
                    <a href="#" class="text-[#000000] font-bold hover:underline">Terms of Service</a> 
                    and 
                    <a href="#" class="text-[#000000] font-bold hover:underline">Privacy Policy</a>
                </label>
            </div>
            @error('terms')
                <p class="text-[10px] text-[#DC2626]">{{ $message }}</p>
            @enderror

            <div class="pt-2">
                <button type="submit" 
                        class="w-full bg-[#000000] border-2 border-[#000000] py-2.5 text-sm font-bold uppercase tracking-wider text-white hover:bg-[#333333] transition-colors">
                    Create Account
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-xs text-[#666666]">
                Already have an account?
                <a href="{{ route('login') }}" class="text-[#000000] font-bold hover:underline">
                    Sign in
                </a>
            </p>
        </div>
    </div>

</body>
</html>