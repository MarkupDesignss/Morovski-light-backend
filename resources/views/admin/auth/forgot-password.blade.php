@extends('layouts.auth')

@section('title', __('admin.forgot_password_title'))

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
                <h2 class="text-2xl font-bold mb-2" style="color: #0B1A20;">{{ __('admin.forgot_password_header') }}</h2>
                <p class="text-sm" style="color: #4B5565;">{{ __('admin.forgot_password_subheader') }}</p>
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

            {{-- Success Message --}}
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 rounded-r-lg" style="border-left-color: #10B981;">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.forgot-password.send') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-2"
                        style="color: #0B1A20;">{{ __('admin.email_label') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5" style="color: #9CA3AF;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:outline-none focus:ring-2 transition"
                            style="background-color: #F9FAFB; focus:ring-color: #0B1A20;"
                            placeholder="{{ __('admin.email_placeholder') }}">
                    </div>
                    <p class="mt-2 text-xs" style="color: #6B7280;">{{ __('admin.forgot_password_email_hint') }}</p>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38); focus:ring-color: #0B1A20;">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        {{ __('admin.send_otp_btn') }}
                    </span>
                </button>
            </form>

            {{-- Back to Login --}}
            <div class="mt-6 text-center">
                <a href="{{ route('admin.loginn') }}" class="inline-flex items-center text-sm hover:underline"
                    style="color: #0B1A20;">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('admin.back_to_login_link') }}
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs mt-6" style="color: #6B7280;">
            &copy; {{ date('Y') }} .Morovski. {{ __('admin.all_rights_reserved') }}
        </p>
    </div>
@endsection
