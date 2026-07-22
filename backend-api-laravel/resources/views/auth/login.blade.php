<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Smart Discussion Forum</title>
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
            padding: 1.5rem;
            margin: 0;
        }
        .login-card {
            background: white;
            border: 1px solid #E5E5E5;
            border-radius: 0; /* square corners */
            max-width: 460px;
            width: 100%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .green-header {
            background-color: #0A574F;
            padding: 2rem 3rem 1.5rem; /* increased left/right padding from 1.5rem to 3rem */
            text-align: center;
            border-radius: 0;
        }
        .green-header h1 {
            color: white;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 0;
        }
        .green-header p {
            color: rgba(255,255,255,0.85);
            font-size: 0.8rem;
            margin-top: 0.3rem;
            letter-spacing: 0.02em;
        }
        .green-header i {
            color: white;
        }
        .form-body {
            padding: 2.5rem 2.5rem 2rem;
        }
        .input-field {
            width: 100%;
            background: #F9F9F9;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            font-size: 0.95rem;
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
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            pointer-events: none;
        }
        .relative {
            position: relative;
        }
        .btn-primary {
            width: 100%;
            background: #0A574F;
            border: none;
            border-radius: 8px;
            padding: 0.85rem;
            color: white;
            font-size: 0.85rem;
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
            width: 17px;
            height: 17px;
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
            padding: 0.75rem 1rem;
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
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #000;
            margin-bottom: 0.3rem;
            display: block;
        }
        .mt-1 { margin-top: 0.25rem; }
        .mt-5 { margin-top: 2rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .space-y-4 > * + * { margin-top: 1rem; }
        .gap-2 { gap: 0.5rem; }
        .text-center { text-align: center; }
        .text-xs { font-size: 0.75rem; }
        .text-10 { font-size: 0.65rem; }
        .text-[#666666] { color: #666; }
        .underline { text-decoration: underline; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .cursor-pointer { cursor: pointer; }
    </style>
</head>
<body>

    <div class="login-card">

        {{-- Header --}}
        <div class="green-header">
            <div class="flex items-center justify-center gap-2">
                <i data-lucide="message-square" style="width:30px;height:30px;color:white;"></i>
                <h1>Smart Discussion Forum</h1>
            </div>
            <p>Sign in to access your workspace</p>
        </div>

        {{-- Form Body --}}
        <div class="form-body">

            {{-- Errors --}}
            @if($errors->any())
                <div class="message-box border border-error bg-error mb-4">
                    <i data-lucide="alert-circle" class="text-error" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="text-xs text-error">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Success --}}
            @if(session('status'))
                <div class="message-box border border-success bg-success mb-4">
                    <i data-lucide="check-circle" class="text-success" style="width:18px;height:18px;flex-shrink:0;margin-top:1px;"></i>
                    <p class="text-xs text-success">{{ session('status') }}</p>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label class="label-text">Workspace Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="input-icon" style="width:18px;height:18px;"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="input-field @error('email') error @enderror"
                               placeholder="you@example.com">
                    </div>
                    @error('email')
                        <p class="text-10 text-error flex items-center gap-1 mt-1">
                            <i data-lucide="alert-circle" style="width:12px;height:12px;"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="label-text">Password</label>
                    <div class="relative">
                        <i data-lucide="lock" class="input-icon" style="width:18px;height:18px;"></i>
                        <input type="password" name="password" required
                               class="input-field"
                               placeholder="••••••••">
                    </div>
                </div>

                {{-- Remember & Forgot --}}
                <div class="flex-between">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-[#666666]">
                        <input type="checkbox" name="remember" class="checkbox-custom">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs text-[#666666] hover:text-[#0A574F] underline">
                        Forgot password?
                    </a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                    <i data-lucide="log-in" style="width:18px;height:18px;"></i>
                    Access Workspace
                </button>
            </form>

            {{-- Register & Help --}}
            <div class="mt-5 text-center space-y-2">
                <p class="text-xs text-[#666666]">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="register-link">Register</a>
                </p>
                <p class="text-10 text-[#666666]">
                    <a href="#" class="hover-green flex items-center justify-center gap-1">
                        <i data-lucide="help-circle" style="width:14px;height:14px;color:#0A574F;"></i>
                        Trouble logging in? Contact Administrator
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