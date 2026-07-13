<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>MORVOSKI | Admin Portal</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!--<link rel="icon" type="image/png" href="{{ asset('logo/MORVOSKI-logo.png') }}">-->
        @php
        $favicon = \App\Models\HeaderMenu::where('type', 'logo')->value('favicon');
    @endphp
    
    <link rel="icon" type="image/png"
          href="{{ $favicon ? asset('storage/' . $favicon) : asset('logo/MORVOSKI-logo.png') }}">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        /* ---------- RESET & BASE ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        /* PRIMARY BACKGROUND: #A28051 (warm elegant earth tone) */
        body {
            background: linear-gradient(145deg, #160c00 0%, #2a1508 50%, #160c00 100%);
            font-family: 'Inter', 'Plus Jakarta Sans', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* organic flowing abstract pattern */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 15% 40%, rgba(255, 245, 225, 0.12) 1.5px, transparent 1.5px),
                radial-gradient(circle at 75% 85%, rgba(255, 245, 225, 0.08) 1px, transparent 1px);
            background-size: 48px 48px, 32px 32px;
            pointer-events: none;
        }

        /* large glowing orbs - depth & luxury */
        body::after {
            content: '';
            position: absolute;
            width: 1000px;
            height: 1000px;
            background: radial-gradient(circle, rgba(11, 26, 32, 0.2) 0%, rgba(0, 0, 0, 0) 70%);
            top: -400px;
            right: -300px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 0;
            pointer-events: none;
        }

        .glow-left {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(200, 180, 147, 0.35) 0%, rgba(162, 128, 81, 0) 75%);
            bottom: -300px;
            left: -200px;
            filter: blur(100px);
            z-index: 0;
            pointer-events: none;
        }

        .glow-center {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(11, 26, 32, 0.12) 0%, transparent 80%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            filter: blur(80px);
            z-index: 0;
        }

        /* main container */
        .auth-container {
            width: 100%;
            max-width: 500px;
            padding: 1.5rem;
            position: relative;
            z-index: 20;
        }

        /* smooth entrance animation */
        .fade-in-up {
            animation: floatGlow 0.9s cubic-bezier(0.15, 0.85, 0.35, 1) forwards;
        }

        @keyframes floatGlow {
            0% {
                opacity: 0;
                transform: translateY(40px) scale(0.96);
                filter: blur(2px);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        /* custom scrollbar hidden */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @yield('styles')
</head>

<body>

    <!-- ambient background layers -->
    <div class="glow-left"></div>
    <div class="glow-center"></div>

    <!-- Language Switcher - refined glass badge -->
    <div x-data="{ open: false }" class="absolute top-6 right-6 z-30">
        {{-- <button @click="open = !open"
            class="flex items-center gap-2 backdrop-blur-xl bg-white/10 px-4 py-2.5 rounded-2xl border border-white/20 text-white font-medium text-sm shadow-lg hover:bg-white/20 transition-all duration-300">
            <i class="fa-solid fa-globe text-xs opacity-80"></i>
            <span>{{ strtoupper(app()->getLocale()) }}</span>
            <i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="{ 'rotate-180': open }"></i>
        </button> --}}

       
    </div>

    <div class="auth-container fade-in-up">
        @yield('content')
    </div>

    @stack('scripts')
</body>

</html>
