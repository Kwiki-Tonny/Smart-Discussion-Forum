<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Smart Discussion Forum') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(145deg, #f8fafc 0%, #eef2f7 100%);
            overflow: hidden;
            position: relative;
        }
        .shape {
            position: fixed;
            border-radius: 50%;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }
        .shape-1 {
            width: 400px;
            height: 400px;
            background: #0A574F;
            top: -150px;
            right: -100px;
        }
        .shape-2 {
            width: 500px;
            height: 500px;
            background: #2563EB;
            bottom: -200px;
            left: -150px;
        }
        .shape-3 {
            width: 250px;
            height: 250px;
            background: #D97706;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .card-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 25px 70px rgba(0,0,0,0.07);
            border-radius: 2.5rem;
            width: 100%;
            max-width: 780px;
            padding: 2.25rem 2.5rem 2rem;
            margin-top: -5vh;
            transition: transform 0.2s ease;
        }
        .card:hover { transform: translateY(-3px); }

        .hero-icon {
            filter: drop-shadow(0 8px 16px rgba(10,87,79,0.18));
        }
        .btn-primary {
            transition: all 0.15s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(10,87,79,0.3);
        }
        .btn-outline {
            transition: all 0.15s ease;
        }
        .btn-outline:hover {
            background: #f0f9f5;
            transform: translateY(-2px);
        }

        .feature-icon {
            background: rgba(10,87,79,0.08);
            border-radius: 14px;
            padding: 0.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .feature-item:hover .feature-icon {
            background: rgba(10,87,79,0.15);
        }

        .fade-up {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.8s ease forwards;
        }
        .fade-up-d1 { animation-delay: 0.1s; }
        .fade-up-d2 { animation-delay: 0.3s; }
        .fade-up-d3 { animation-delay: 0.5s; }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .feature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .feature-item {
            text-align: center;
        }
        .feature-item p {
            font-size: 0.8rem;
            line-height: 1.5;
            margin-top: 0.25rem;
            color: #475569;
        }
        .feature-item h4 {
            font-size: 0.75rem;
            font-weight: 700;
            color: #0A574F;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-top: 0.35rem;
        }

        @media (max-width: 600px) {
            .card { padding: 1.5rem 1.25rem; max-width: 100%; margin-top: 0; }
            .feature-grid { grid-template-columns: 1fr; gap: 0.75rem; }
            .feature-item { display: flex; align-items: center; gap: 0.75rem; text-align: left; }
            .feature-item p { margin-top: 0; }
            .feature-item h4 { margin-top: 0; }
        }
    </style>
</head>
<body>

    {{-- Full‑viewport blobs --}}
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>

    {{-- Card wrapper --}}
    <div class="card-wrapper">

        <div class="card">

            {{-- Logo & Headline --}}
            <div class="text-center fade-up">
                <div class="flex justify-center mb-3">
                    <div class="hero-icon bg-[#0A574F] p-4 rounded-2xl shadow-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            <path d="M8 10h.01"/><path d="M12 10h.01"/><path d="M16 10h.01"/>
                        </svg>
                    </div>
                </div>

                <h1 class="text-4xl md:text-5xl font-extrabold text-[#0A574F] tracking-tight">
                    Smart Discussion
                </h1>
                <p class="text-xl md:text-2xl font-light text-[#334155] mt-0.5">
                    Engage, Learn, and Collaborate
                </p>

                <p class="text-base text-[#475569] max-w-md mx-auto mt-2">
                    A modern space for meaningful conversations, interactive learning, and shared growth.
                </p>

                {{-- CTAs – unchanged names --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-5 fade-up-d1">
                    <a href="{{ route('login') }}"
                       class="btn-primary w-full sm:w-auto px-7 py-3 bg-[#0A574F] text-white font-semibold rounded-xl shadow-lg hover:bg-[#08443e] flex items-center justify-center gap-2 text-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="btn-outline w-full sm:w-auto px-7 py-3 bg-white text-[#0A574F] font-semibold rounded-xl shadow-md border border-[#0A574F] hover:bg-[#f0f9f5] flex items-center justify-center gap-2 text-sm">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                        Create Account
                    </a>
                </div>
            </div>

            {{-- Feature highlights – functional but human --}}
            <div class="feature-grid fade-up-d2">
                <div class="feature-item">
                    <div class="flex justify-center sm:justify-start">
                        <span class="feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0A574F" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                    </div>
                    <h4>Join the Conversation</h4>
                    <p>Start or join threaded discussions, share your voice, and get real‑time replies from people who care.</p>
                </div>
                <div class="feature-item">
                    <div class="flex justify-center sm:justify-start">
                        <span class="feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0A574F" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                    </div>
                    <h4>Test & Grow</h4>
                    <p>Take interactive quizzes, get instant scores, and track your progress – all while having fun.</p>
                </div>
                <div class="feature-item">
                    <div class="flex justify-center sm:justify-start">
                        <span class="feature-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0A574F" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                        </span>
                    </div>
                    <h4>Collaborate & Achieve</h4>
                    <p>Work in groups, share resources, and build knowledge together with a supportive community.</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="text-center text-[10px] text-[#94A3B8] mt-3 fade-up-d3">
                &copy; {{ date('Y') }} Smart Discussion Forum
            </div>

        </div>
    </div>

</body>
</html>