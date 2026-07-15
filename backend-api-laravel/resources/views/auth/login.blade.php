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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-wrapper {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 50px -12px rgba(0,0,0,0.12);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #0f766e 0%, #115e56 100%);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .login-header .brand-icon {
            background: rgba(255,255,255,0.15);
            border-radius: 14px;
            padding: 0.6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .login-header h1 {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
        }
        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 0.7rem;
            font-weight: 500;
            margin: 0;
        }
        .login-body {
            padding: 2rem;
        }
        .login-input {
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            width: 100%;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        .login-input:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.08);
        }
        .login-input::placeholder {
            color: #9ca3af;
        }
        .input-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }
        .input-label i {
            margin-right: 0.4rem;
            color: #0f766e;
        }
        .icon-wrapper {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        .toggle-password:hover {
            color: #1e293b;
        }
        .login-btn {
            background: #0f766e;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            width: 100%;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: white;
            transition: all 0.25s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .login-btn:hover {
            background: #115e56;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px -6px rgba(15, 118, 110, 0.3);
        }
        .login-btn:active {
            transform: scale(0.97);
        }
        .link-primary {
            color: #0f766e;
            font-weight: 600;
            text-decoration: none;
        }
        .link-primary:hover {
            color: #115e56;
            text-decoration: underline;
        }
        .link-muted {
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
        }
        .link-muted:hover {
            color: #1e293b;
            text-decoration: underline;
        }
        .badge-group {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .badge-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.7rem;
            font-weight: 500;
            color: #4b5563;
        }
        .badge-item i {
            color: #0f766e;
        }
        .divider {
            border: none;
            border-top: 1.5px solid #e5e7eb;
            margin: 1.5rem 0 1rem;
        }
        .error-box, .success-box {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            margin-bottom: 1.2rem;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .success-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }
        .legal-links {
            text-align: center;
            font-size: 0.65rem;
            color: #9ca3af;
            margin-top: 1rem;
        }
        .legal-links a {
            color: #6b7280;
            text-decoration: none;
        }
        .legal-links a:hover {
            color: #1e293b;
        }
        .field-group { margin-bottom: 1.2rem; }
        @media (max-width: 480px) {
            .login-header { padding: 1.2rem; }
            .login-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        {{-- Header --}}
        <div class="login-header">
            <div class="brand-icon">
                <i data-lucide="messages-square" class="size-5"></i>
            </div>
            <div>
                <h1>Smart Discussion</h1>
                <p>Web Client Workspace Portal</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="login-body">

            @if($errors->any())
                <div class="error-box">
                    <i data-lucide="alert-circle" class="size-4"></i>
                    @foreach($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            @endif

            @if(session('status'))
                <div class="success-box">
                    <i data-lucide="check-circle" class="size-4"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div class="field-group">
                    <label class="input-label">
                        <i data-lucide="mail" class="size-3.5"></i> Workspace Email
                    </label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="login-input @error('email') border-red-500 @enderror"
                               placeholder="lecturer@example.com">
                        <i data-lucide="mail" class="icon-wrapper size-4"></i>
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field-group">
                    <label class="input-label">
                        <i data-lucide="key" class="size-3.5"></i> Password
                    </label>
                    <div class="relative">
                        <input type="password" name="password" required id="password"
                               class="login-input @error('password') border-red-500 @enderror"
                               placeholder="••••••••">
                        <i data-lucide="lock" class="icon-wrapper size-4"></i>
                        <button type="button" id="togglePassword" class="toggle-password">
                            <i data-lucide="eye" class="size-4"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember & Forgot --}}
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-[#0f766e] focus:ring-2 focus:ring-[#0f766e] focus:ring-offset-2">
                        <span class="text-slate-700 font-medium">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="link-primary text-sm">
                        Forgot password?
                    </a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="login-btn">
                    <i data-lucide="log-in" class="size-4"></i> Access Workspace
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-5 text-center space-y-1">
                <p class="text-sm text-slate-600">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="link-primary font-semibold">Register</a>
                </p>
                <p class="text-xs text-slate-500">
                    <a href="#" class="link-muted">Trouble logging in? Contact Administrator</a>
                </p>
            </div>

            {{-- Badges --}}
            <hr class="divider">
            <div class="badge-group">
                <span class="badge-item">
                    <i data-lucide="shield-check" class="size-3.5"></i> Secure
                </span>
                <span class="badge-item">
                    <i data-lucide="lock" class="size-3.5"></i> Encrypted
                </span>
                <span class="badge-item">
                    <span class="w-2 h-2 bg-emerald-600 rounded-full inline-block animate-pulse"></span> Online
                </span>
            </div>

            <div class="legal-links">
                <a href="#">Privacy Policy</a>
                <span class="mx-2">•</span>
                <a href="#">Terms of Service</a>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const toggleBtn = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            if (toggleBtn && passwordInput) {
                toggleBtn.addEventListener('click', function() {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
                        lucide.createIcons();
                    }
                });
            }
        });
    </script>

</body>
</html>