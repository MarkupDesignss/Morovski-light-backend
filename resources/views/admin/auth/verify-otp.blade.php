@extends('layouts.auth')

@section('title', __('admin.verify_otp_title'))

@section('content')
    <div class="w-full max-w-md">
        {{-- Logo/Brand Section --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
                style="background: linear-gradient(135deg, #0B1A20, #162E38);">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold" style="color: #0B1A20;">{{ config('app.name') }}</h1>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl border-0"
            style="box-shadow: 0 20px 40px -15px rgba(11, 26, 32, 0.15);">

            {{-- Header --}}
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold mb-2" style="color: #0B1A20;">{{ __('admin.verify_otp_header') }}</h2>
                <p class="text-sm" style="color: #4B5565;">{{ __('admin.verify_otp_subheader') }}</p>
                @if (session('email'))
                    <p class="text-xs mt-2 font-medium" style="color: #0B1A20;">{{ session('email') }}</p>
                @endif
            </div>

            {{-- Error Message --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 rounded-r-lg" style="border-left-color: #DC2626;">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.otp.verify') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">

                <div>
                    <label class="block text-sm font-medium mb-2"
                        style="color: #0B1A20;">{{ __('admin.otp_code_label') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5" style="color: #9CA3AF;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4">
                                </path>
                            </svg>
                        </div>
                        <input type="text" name="otp" maxlength="6"
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 text-center text-2xl tracking-widest font-mono focus:outline-none focus:ring-2 transition"
                            style="background-color: #F9FAFB; focus:ring-color: #0B1A20; letter-spacing: 0.5em;"
                            placeholder="••••••" autocomplete="off">
                    </div>
                    <p class="mt-2 text-xs text-center" style="color: #6B7280;">{{ __('admin.otp_code_hint') }}</p>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38); focus:ring-color: #0B1A20;">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('admin.verify_otp_btn') }}
                    </span>
                </button>
            </form>

            {{-- Resend OTP --}}
            <div class="mt-6 text-center">
                <p class="text-sm" style="color: #6B7280;">
                    {{ __('admin.resend_otp_text') }}
                <form method="POST" action="{{ route('admin.forgot-password.send') }}" class="inline">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('email') }}">
                    <button type="submit" class="font-medium hover:underline focus:outline-none" style="color: #0B1A20;">
                        {{ __('admin.resend_otp_btn') }}
                    </button>
                </form>
                </p>
            </div>

            {{-- Back to Forgot Password --}}
            <div class="mt-4 text-center">
                <a href="{{ route('admin.forgot-password.form') }}"
                    class="inline-flex items-center text-sm hover:underline" style="color: #0B1A20;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.use_different_email_link') }}
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs mt-6" style="color: #6B7280;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('admin.all_rights_reserved') }}
        </p>
    </div>
@endsection
