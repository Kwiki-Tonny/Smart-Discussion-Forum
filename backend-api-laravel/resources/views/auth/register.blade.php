<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - Smart Discussion Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * {
            font-family: 'Segoe UI', Inter, sans-serif;
            box-sizing: border-box;
        }
        body {
            background: #F9F9F9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
            margin: 0;
        }
        .register-card {
            background: white;
            border: 1px solid #E5E5E5;
            border-radius: 0;
            max-width: 420px;  /* slightly narrower for a tighter fit */
            width: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .green-header {
            background-color: #0A574F;
            padding: 1.5rem 2rem 1rem;  /* reduced */
            text-align: center;
            border-radius: 0;
        }
        .green-header h1 {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 0;
        }
        .green-header p {
            color: rgba(255,255,255,0.85);
            font-size: 0.75rem;
            margin-top: 0.2rem;
            letter-spacing: 0.02em;
        }
        .green-header i {
            color: white;
        }
        .form-body {
            padding: 1.5rem 2rem 1.5rem;  /* reduced */
        }
        .input-field {
            width: 100%;
            background: #F9F9F9;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            padding: 0.6rem 0.9rem 0.6rem 2.4rem;  /* tighter */
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            border-color: #0A574F;
            background: white;
            box-shadow: 0 0 0 3px rgba(10, 87, 79, 0.12);
        }
        .input-field.error {
            border-color: #DC2626;
        }
        .input-icon {
            position: absolute;
            left: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            pointer-events: none;
            width: 16px;
            height: 16px;
        }
        .relative {
            position: relative;
        }
        .btn-primary {
            width: 100%;
            background: #0A574F;
            border: none;
            border-radius: 8px;
            padding: 0.6rem;  /* reduced */
            color: white;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: #08443e;
        }
        .checkbox-custom {
            accent-color: #0A574F;
            width: 16px;
            height: 16px;
        }
        .register-link {
            color: #0A574F;
            font-weight: 700;
        }
        .register-link:hover {
            text-decoration: underline;
        }
        .hover-green:hover {
            color: #0A574F;
        }
        .message-box {
            border-radius: 8px;
            padding: 0.6rem 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .text-error { color: #DC2626; }
        .text-success { color: #0A574F; }
        .border-error { border-color: #DC2626; }
        .border-success { border-color: #0A574F; }
        .bg-error { background: #FEF2F2; }
        .bg-success { background: #F0FDF4; }
        .flex-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .label-text {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #000;
            margin-bottom: 0.15rem;  /* reduced */
            display: block;
        }
        .mt-1 { margin-top: 0.25rem; }
        .mt-5 { margin-top: 2rem; }
        .mb-4 { margin-bottom: 1rem; }
        .space-y-3 > * + * { margin-top: 0.75rem; }  /* smaller gap */
        .gap-2 { gap: 0.5rem; }
        .text-center { text-align: center; }
        .text-xs { font-size: 0.7rem; }
        .text-10 { font-size: 0.6rem; }
        .text-[#666666] { color: #666; }
        .underline { text-decoration: underline; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .cursor-pointer { cursor: pointer; }
        .flex-wrap { flex-wrap: wrap; }
        .mt-0\.5 { margin-top: 0.125rem; }
        .mt-3 { margin-top: 0.75rem; }
    </style>
</head>
<body>

    <div class="register-card">

        {{-- Header --}}
        <div class="green-header">
            <div class="flex items-center justify-center gap-2">
                <i data-lucide="message-square" style="width:26px;height:26px;color:white;"></i>
                <h1>Smart Discussion Forum</h1>
            </div>
            <p>Create your account</p>
        </div>

        {{-- Form Body --}}
        <div class="form-body">

            {{-- Errors --}}
            @if($errors->any())
                <div class="message-box border border-error bg-error mb-4">
                    <i data-lucide="alert-circle" class="text-error" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-xs text-error">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Success (if any) --}}
            @if(session('status'))
                <div class="message-box border border-success bg-success mb-4">
                    <i data-lucide="check-circle" class="text-success" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;"></i>
                    <p class="text-xs text-success">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Registration Form --}}
            <form method="POST" action="{{ route('register.submit') }}" class="space-y-3">
                @csrf

                {{-- Full Name --}}
                <div>
                    <label class="label-text">Full Name</label>
                    <div class="relative">
                        <i data-lucide="user" class="input-icon"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="input-field @error('name') error @enderror"
                               placeholder="John Doe">
                    </div>
                    @error('name')
                        <p class="text-10 text-error flex items-center gap-1 mt-0.5">
                            <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="label-text">Email Address</label>
                    <div class="relative">
                        <i data-lucide="mail" class="input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="input-field @error('email') error @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <p class="text-10 text-error flex items-center gap-1 mt-0.5">
                            <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="label-text">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="input-icon"></i>
                        <input type="password" name="password" required
                               class="input-field @error('password') error @enderror"
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="text-10 text-error flex items-center gap-1 mt-0.5">
                            <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-10 text-[#666666] mt-0.5">Minimum 8 characters</p>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="label-text">Confirm Password</label>
                    <div class="relative">
                        <i data-lucide="key" class="input-icon"></i>
                        <input type="password" name="password_confirmation" required
                               class="input-field"
                               placeholder="••••••••">
                    </div>
                </div>

                {{-- Terms Checkbox --}}
                <div class="flex items-start gap-2 pt-0.5">
                    <input type="checkbox" name="terms" required
                           class="checkbox-custom mt-0.5">
                    <label class="text-xs text-[#666666] leading-relaxed">
                        I agree to the
                        <a href="#" class="text-[#0A574F] font-bold hover:underline">Terms of Service</a>
                        and
                        <a href="#" class="text-[#0A574F] font-bold hover:underline">Privacy Policy</a>
                    </label>
                </div>
                @error('terms')
                    <p class="text-10 text-error flex items-center gap-1">
                        <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                        {{ $message }}
                    </p>
                @enderror

                {{-- Submit Button --}}
                <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" style="width:16px;height:16px;"></i>
                    Create Account
                </button>
            </form>

            {{-- Login Link & Help --}}
            <div class="mt-3 text-center space-y-1">
                <p class="text-xs text-[#666666]">
                    Already have an account?
                    <a href="{{ route('login') }}" class="register-link">Sign in</a>
                </p>
                <p class="text-10 text-[#666666]">
                    <a href="#" class="hover-green flex items-center justify-center gap-1">
                        <i data-lucide="help-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                        Need help? Contact Administrator
                    </a>
                </p>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>