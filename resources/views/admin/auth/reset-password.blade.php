@extends('layouts.auth')

@section('title', __('admin.reset_password_title'))

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
                <h2 class="text-2xl font-bold mb-2" style="color: #0B1A20;">{{ __('admin.set_new_password_header') }}</h2>
                <p class="text-sm" style="color: #4B5565;">{{ __('admin.set_new_password_subheader') }}</p>
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

            {{-- Password Requirements --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-xl" style="background-color: #F9FAFB;">
                <h4 class="text-xs font-semibold mb-2" style="color: #0B1A20;">{{ __('admin.password_requirements_title') }}
                </h4>
                <ul class="space-y-1 text-xs" style="color: #6B7280;">
                    <li class="flex items-center">
                        <svg class="w-3 h-3 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ __('admin.password_requirement_min_length') }}
                    </li>
                </ul>
            </div>

            <form method="POST" action="{{ route('admin.reset.password') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">

                <div>
                    <label class="block text-sm font-medium mb-2"
                        style="color: #0B1A20;">{{ __('admin.new_password_label') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5" style="color: #9CA3AF;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <input type="password" name="password"
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:outline-none focus:ring-2 transition"
                            style="background-color: #F9FAFB; focus:ring-color: #0B1A20;"
                            placeholder="{{ __('admin.new_password_placeholder') }}">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2"
                        style="color: #0B1A20;">{{ __('admin.confirm_password_label') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5" style="color: #9CA3AF;" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                        </div>
                        <input type="password" name="password_confirmation" required
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border-0 rounded-xl text-gray-900 focus:outline-none focus:ring-2 transition"
                            style="background-color: #F9FAFB; focus:ring-color: #0B1A20;"
                            placeholder="{{ __('admin.confirm_password_placeholder') }}">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background: linear-gradient(135deg, #0B1A20, #162E38); focus:ring-color: #0B1A20;">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        {{ __('admin.reset_password_btn') }}
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
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('admin.all_rights_reserved') }}
        </p>
    </div>

    <script>
        // Optional: Add password strength indicator
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    // You can add password strength logic here
                    console.log('Password strength check');
                });
            }
        });
    </script>
@endsection
