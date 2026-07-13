<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Account Status Update</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.5;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.2);
        }

        .email-header {
            background: linear-gradient(135deg, #2c3e50 0%, #1e2a36 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: shine 6s infinite;
        }

        @keyframes shine {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin: 0;
            letter-spacing: -0.5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .logo span {
            color: #60a5fa;
        }

        .email-body {
            padding: 40px 30px;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        }

        .greeting {
            font-size: 28px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 10px 0;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .status-approved {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px 25px;
            border-radius: 20px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(5, 150, 105, 0.3);
            position: relative;
            overflow: hidden;
        }

        .status-approved::before {
            content: '✨';
            position: absolute;
            top: -10px;
            left: -10px;
            font-size: 100px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .status-approved::after {
            content: '✨';
            position: absolute;
            bottom: -20px;
            right: -10px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(15deg);
        }

        .status-rejected {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            color: white;
            padding: 30px 25px;
            border-radius: 20px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(185, 28, 28, 0.3);
            position: relative;
            overflow: hidden;
        }

        .status-rejected::before {
            content: '⚠️';
            position: absolute;
            top: -10px;
            left: -10px;
            font-size: 100px;
            opacity: 0.1;
            transform: rotate(-15deg);
        }

        .status-rejected::after {
            content: '⚠️';
            position: absolute;
            bottom: -20px;
            right: -10px;
            font-size: 120px;
            opacity: 0.1;
            transform: rotate(15deg);
        }

        .status-icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            backdrop-filter: blur(10px);
        }

        .status-title {
            font-size: 24px;
            font-weight: 700;
            margin: 10px 0 5px 0;
        }

        .status-message {
            font-size: 16px;
            opacity: 0.95;
            margin: 0;
            line-height: 1.6;
        }

        .highlight {
            background: rgba(255, 255, 255, 0.2);
            padding: 3px 10px;
            border-radius: 30px;
            display: inline-block;
            font-weight: 600;
            margin-top: 15px;
            backdrop-filter: blur(5px);
        }

        .features-list {
            margin: 30px 0;
            padding: 0;
            list-style: none;
        }

        .features-list li {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            color: #4b5563;
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 28px;
            height: 28px;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: #0369a1;
            font-weight: bold;
        }

        .support-box {
            background: #f8fafc;
            border-left: 4px solid #2c3e50;
            padding: 20px 25px;
            margin: 30px 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .support-text {
            color: #475569;
            margin: 5px 0;
        }

        .contact-button {
            display: inline-block;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0 10px 0;
            box-shadow: 0 10px 20px -5px rgba(44, 62, 80, 0.3);
            transition: all 0.3s ease;
        }

        .contact-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(44, 62, 80, 0.4);
        }

        .email-footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .company-name {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 5px 0;
        }

        .footer-text {
            color: #64748b;
            font-size: 14px;
            margin: 5px 0;
        }

        .social-links {
            margin: 20px 0 10px 0;
        }

        .social-link {
            display: inline-block;
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 50%;
            margin: 0 5px;
            line-height: 36px;
            color: #2c3e50;
            text-decoration: none;
            font-size: 18px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
            color: #3498db;
        }

        hr {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 25px 0;
        }

        @media (max-width: 600px) {
            .email-wrapper {
                margin: 20px;
                border-radius: 20px;
            }

            .email-header {
                padding: 30px 20px;
            }

            .email-body {
                padding: 30px 20px;
            }

            .greeting {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        {{-- Header --}}
        <div class="email-header">
            <h1 class="logo">{{ config('app.name') }} <span>●</span></h1>
        </div>

        {{-- Body --}}
        <div class="email-body">
            <h2 class="greeting">Hello {{ $user->full_name }},</h2>

            @if ($status == 'approved')
                {{-- Approved Status --}}
                <div class="status-approved">
                    <div class="status-icon">🎉</div>
                    <h3 class="status-title">Congratulations! 🎊</h3>
                    <p class="status-message">Your business account has been <strong>approved</strong>.</p>
                    <div class="highlight">✓ Verified Business Account</div>
                </div>

                <p style="font-size: 18px; color: #1f2937; margin: 25px 0 15px 0; font-weight: 500;">
                    You can now access all business features:
                </p>

                <ul class="features-list">
                    <li>
                        <span class="feature-icon">📊</span>
                        Advanced analytics dashboard
                    </li>
                    <li>
                        <span class="feature-icon">🚀</span>
                        Priority customer support
                    </li>
                    <li>
                        <span class="feature-icon">💼</span>
                        Business profile showcase
                    </li>
                    <li>
                        <span class="feature-icon">📈</span>
                        Unlimited service listings
                    </li>
                    <li>
                        <span class="feature-icon">🤝</span>
                        Partnership opportunities
                    </li>
                </ul>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="#" class="contact-button">
                        🚀 Access Business Dashboard
                    </a>
                </div>
            @else
                {{-- Rejected Status --}}
                <div class="status-rejected">
                    <div class="status-icon">📋</div>
                    <h3 class="status-title">Application Status Update</h3>
                    <p class="status-message">Your business account has been <strong>rejected</strong>.</p>
                </div>

                <div class="support-box">
                    <p style="font-size: 16px; color: #1e293b; margin-top: 0; font-weight: 500;">
                        🔍 Common reasons for rejection:
                    </p>
                    <ul style="color: #475569; padding-left: 20px; margin: 15px 0;">
                        <li style="margin: 8px 0;">Incomplete or unclear documentation</li>
                        <li style="margin: 8px 0;">Business information mismatch</li>
                        <li style="margin: 8px 0;">Unable to verify business details</li>
                    </ul>
                </div>

                <p style="color: #475569; margin: 25px 0; line-height: 1.8;">
                    We encourage you to review your application and try again with
                    accurate information. Our support team is here to help you through
                    the process.
                </p>

                {{-- <div style="text-align: center; margin: 30px 0;">
                    <a href="#" class="contact-button">
                        📧 Contact Support Team
                    </a>
                </div> --}}

                <p style="color: #64748b; font-size: 15px; text-align: center; margin: 20px 0 0 0;">
                    We typically respond within 24 hours
                </p>
            @endif

            <hr>

            {{-- Support Section --}}
            <div style="text-align: center;">
                <p style="color: #4b5563; margin: 0 0 10px 0; font-weight: 500;">
                    Need assistance? We're here to help!
                </p>
                <p style="color: #6b7280; font-size: 14px; margin: 0;">
                    📞 +1 (555) 123-4567<br>
                    ✉️ support@example.com
                </p>
            </div>
        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <h3 class="company-name">{{ config('app.name') }}</h3>
            <p class="footer-text">Building better businesses together</p>

            <div class="social-links">
                <a href="#" class="social-link">𝕏</a>
                <a href="#" class="social-link">in</a>
                <a href="#" class="social-link">f</a>
                <a href="#" class="social-link">ig</a>
            </div>

            <p class="footer-text" style="margin-top: 20px;">
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                123 Business Avenue, Suite 100, San Francisco, CA 94105
            </p>

            <p class="footer-text" style="font-size: 12px; margin-top: 15px;">
                This is an automated message from {{ config('app.name') }}. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>

</html>
