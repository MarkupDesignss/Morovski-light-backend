@extends('layouts.auth')

@section('title', __('admin.login_page_title') . ' | Morvoski')

@section('content')
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* .page-bg {
            min-height: 100vh;
            background: linear-gradient(145deg, #160c00 0%, #2a1508 50%, #160c00 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        } */

        .page-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 40% at 20% 20%, rgba(162, 128, 81, 0.12) 0%, transparent 70%),
                radial-gradient(ellipse 50% 35% at 80% 80%, rgba(197, 166, 114, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .luxury-card {
            background: linear-gradient(160deg, #fdf8f0 0%, #faf3e8 60%, #f5ead6 100%);
            border: 1px solid rgba(162, 128, 81, 0.35);
            border-radius: 24px;
            padding: 2.5rem 2rem 2rem;
            width: 100%;
            max-width: 420px;
            position: relative;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(162, 128, 81, 0.1),
                0 24px 60px rgba(0, 0, 0, 0.45),
                0 4px 16px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .luxury-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #A28051, #C5A672, #A28051, transparent);
        }

        .luxury-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(162, 128, 81, 0.4), transparent);
        }

        .corner-tl,
        .corner-tr,
        .corner-bl,
        .corner-br {
            position: absolute;
            width: 14px;
            height: 14px;
        }

        .corner-tl {
            top: 14px;
            left: 14px;
            border-top: 1px solid rgba(162, 128, 81, 0.45);
            border-left: 1px solid rgba(162, 128, 81, 0.45);
            border-radius: 3px 0 0 0;
        }

        .corner-tr {
            top: 14px;
            right: 14px;
            border-top: 1px solid rgba(162, 128, 81, 0.45);
            border-right: 1px solid rgba(162, 128, 81, 0.45);
            border-radius: 0 3px 0 0;
        }

        .corner-bl {
            bottom: 14px;
            left: 14px;
            border-bottom: 1px solid rgba(162, 128, 81, 0.45);
            border-left: 1px solid rgba(162, 128, 81, 0.45);
            border-radius: 0 0 0 3px;
        }

        .corner-br {
            bottom: 14px;
            right: 14px;
            border-bottom: 1px solid rgba(162, 128, 81, 0.45);
            border-right: 1px solid rgba(162, 128, 81, 0.45);
            border-radius: 0 0 3px 0;
        }

        .ornament {
            text-align: center;
            margin-bottom: 0.25rem;
            color: rgba(162, 128, 81, 0.5);
            font-size: 11px;
            letter-spacing: 6px;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
        }

        .logo-ring {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(145deg, #f0e4cc, #e8d5b4);
            border: 1.5px solid rgba(162, 128, 81, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 0.75rem;
            box-shadow:
                0 2px 12px rgba(162, 128, 81, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .logo-ring::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1px solid rgba(162, 128, 81, 0.2);
        }

        .logo-ring img {
            width: 70%;
            height:10%;
            object-fit: contain;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 4px;
            color: #2a1a05;
            font-family: Georgia, serif;
            text-align: center;
        }

        .portal-sub {
            display: block;
            text-align: center;
            font-size: 10px;
            letter-spacing: 3px;
            color: #A28051;
            text-transform: uppercase;
            margin-top: 0.15rem;
            margin-bottom: 1.5rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .divider-line {
            flex: 1;
            height: 0.5px;
            background: linear-gradient(90deg, transparent, rgba(162, 128, 81, 0.35), transparent);
        }

        .divider-gem {
            width: 6px;
            height: 6px;
            background: #A28051;
            transform: rotate(45deg);
            opacity: 0.6;
        }

        .glass-success {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(209, 250, 229, 0.6);
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #065f46;
            margin-bottom: 1.25rem;
            transition: opacity 0.5s ease;
        }

        .glass-success.fade-out {
            opacity: 0;
        }

        .field-group {
            margin-bottom: 1.1rem;
        }

        .field-label {
            display: block;
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #8a6a3a;
            margin-bottom: 0.45rem;
            font-weight: 500;
        }

        .field-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .field-icon {
            position: absolute;
            left: 14px;
            color: rgba(162, 128, 81, 0.65);
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }

        .field-input {
            width: 100%;
            padding: 11px 14px 11px 38px;
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(162, 128, 81, 0.3);
            border-radius: 10px;
            font-size: 14px;
            color: #2a1a05;
            outline: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .field-input::placeholder {
            color: rgba(120, 88, 42, 0.45);
            font-size: 13px;
        }

        .field-input:focus {
            border-color: #A28051;
            background: rgba(255, 255, 255, 0.75);
            box-shadow: 0 0 0 3px rgba(162, 128, 81, 0.12);
        }

        .toggle-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: rgba(162, 128, 81, 0.6);
            cursor: pointer;
            font-size: 13px;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-btn:hover {
            color: #A28051;
        }

        .submit-btn {
            width: 100%;
            margin-top: 0.5rem;
            padding: 13px;
            background: linear-gradient(135deg, #2a1a05 0%, #1a0f00 100%);
            border: 1px solid rgba(162, 128, 81, 0.5);
            border-radius: 10px;
            color: #d4b483;
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(162, 128, 81, 0.15) 0%, transparent 60%);
            opacity: 0;
            transition: opacity 0.25s;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #3d2610 0%, #2a1a05 100%);
            border-color: #A28051;
            color: #e8c98a;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn i {
            font-size: 13px;
            color: #A28051;
        }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 12px;
            color: rgba(162, 128, 81, 0.7);
            text-decoration: none;
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #A28051;
        }
    </style>

    <div class="page-bg">
        <div class="luxury-card">
            <div class="corner-tl"></div>
            <div class="corner-tr"></div>
            <div class="corner-bl"></div>
            <div class="corner-br"></div>

            <div class="ornament">✦ &nbsp; &nbsp; ✦ &nbsp; &nbsp; ✦</div>

            <!-- Logo -->
            <div class="logo-wrap">
                <!--<div class="logo-ring">-->
                    <img src="{{ asset('logo/MORVOSKI-logo.png') }}" style="max-width:70%;height:70%;" alt="Morovski">
                <!--</div>-->
                <!--<div class="brand-title">MOROVSKI</div>-->
                <span class="portal-sub">{{ __('admin.admin_portal_subtitle') }}</span>
            </div>

            <div class="divider">
                <div class="divider-line"></div>
                <div class="divider-gem"></div>
                <div class="divider-line"></div>
            </div>

            <!-- Success message -->
            @if (session('success'))
                <div class="glass-success" id="successMessage">
                    <i class="fa-regular fa-circle-check" style="color:#059669; font-size:16px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf

                <!-- Email -->
                <div class="field-group">
                    <label class="field-label">{{ __('admin.email_label') }}</label>
                    <div class="field-wrap">
                        <i class="fa-regular fa-envelope field-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" class="field-input"
                            placeholder="{{ __('admin.email_placeholder') }}" autocomplete="email">
                    </div>
                </div>

                <!-- Password -->
                <div class="field-group">
                    <label class="field-label">{{ __('admin.password_label') }}</label>
                    <div class="field-wrap">
                        <i class="fa-solid fa-lock field-icon"></i>
                        <input type="password" name="password" id="luxPassword" class="field-input"
                            placeholder="{{ __('admin.password_placeholder') }}" autocomplete="current-password">
                        <button type="button" onclick="toggleLuxPassword()" class="toggle-btn"
                            aria-label="Toggle password visibility">
                            <i id="luxEyeIcon" class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fa-regular fa-circle-check"></i>
                    {{ __('admin.sign_in_btn') }}
                </button>

                <a href="{{ route('admin.forgot-password.form') }}" class="forgot-link">
                    {{ __('admin.forgot_password_link') }} &nbsp;<i class="fa-regular fa-arrow-right"
                        style="font-size:11px;"></i>
                </a>
            </form>

            <div class="ornament" style="margin-top:1.5rem; margin-bottom:0;">✦ &nbsp; &nbsp; ✦ &nbsp; &nbsp; ✦</div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __('admin.access_denied_title') }}',
                    text: @json($errors->first()),
                    confirmButtonText: '{{ __('admin.try_again_btn') }}',
                    confirmButtonColor: '#A28051',
                    background: '#FFF9F0',
                    color: '#1F2A2E',
                    backdrop: 'rgba(0,0,0,0.4)',
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-amber-200',
                        confirmButton: 'px-6 py-2.5 rounded-full font-semibold bg-[#2a1a05] text-[#d4b483] hover:bg-[#A28051] transition'
                    }
                });
            });
        </script>
    @endif

    <script>
        function toggleLuxPassword() {
            const pwdField = document.getElementById('luxPassword');
            const eye = document.getElementById('luxEyeIcon');
            if (pwdField.type === 'text') {
                pwdField.type = 'password';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                pwdField.type = 'text';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }

        setTimeout(function() {
            const successMsg = document.getElementById('successMessage');
            if (successMsg) {
                successMsg.classList.add('fade-out');
                setTimeout(() => {
                    if (successMsg) successMsg.style.display = 'none';
                }, 500);
            }
        }, 3800);
    </script>
@endsection
