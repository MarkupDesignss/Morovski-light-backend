<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Morovski Lights | Account Credentials</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Jost:wght@200;300;400;500;600&display=swap" rel="stylesheet" />
    <style type="text/css">
        /* RESET & GLOBAL */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #0d0c0b;
            font-family: 'Jost', sans-serif;
            color: #e8dfd0;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 640px;
            margin: 0 auto;
            background-color: #0d0c0b;
            width: 100%;
        }
        /* RESPONSIVE */
        @media only screen and (max-width: 620px) {
            .email-wrapper {
                max-width: 100%;
            }
            .content, .hero, .footer {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .features {
                display: block !important;
                width: 100% !important;
            }
            .feature-col {
                display: block !important;
                width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #2a2318;
                padding: 28px 16px !important;
            }
            .feature-col:last-child {
                border-bottom: none;
            }
            .credentials-card {
                padding: 24px 20px !important;
            }
            .hero-title {
                font-size: 32px !important;
            }
        }

        /* HEADER STYLES */
        .header {
            background: linear-gradient(180deg, #0d0c0b 0%, #13110e 100%);
            padding: 28px 28px 26px;
            text-align: center;
            border-bottom: 1px solid #2a2318;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: "";
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 320px;
            height: 200px;
            background: radial-gradient(ellipse at center, rgba(184,140,60,0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .logo-img {
            max-width: 120px;
            border-radius: 50%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: brightness(1.05);
        }
        .tagline {
            font-family: 'Jost', sans-serif;
            font-weight: 200;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-top: 10px;
        }

        /* HERO BANNER */
        .hero {
            background: #13110e;
            padding: 48px 48px 44px;
            text-align: center;
            position: relative;
            border-bottom: 1px solid #2a2318;
        }
        .hero::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 1px;
            background: #b88c3c;
        }
        .hero-eyebrow {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-bottom: 20px;
        }
        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 42px;
            line-height: 1.2;
            color: #f0e8d8;
            margin-bottom: 18px;
        }
        .hero-title em {
            font-style: italic;
            color: #d4a84b;
        }
        .hero-body {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 14px;
            line-height: 1.8;
            color: #9e9284;
            max-width: 460px;
            margin: 0 auto;
        }

        /* CONTENT CORE */
        .content {
            background: #0d0c0b;
            padding: 0 48px 48px;
        }

        /* WELCOME CARD */
        .welcome-card {
            background: rgba(19, 17, 14, 0.9);
            border-radius: 28px;
            border: 0.5px solid rgba(184,140,60,0.35);
            padding: 32px 32px;
            margin: 32px 0 28px;
            backdrop-filter: blur(2px);
        }
        .welcome-salute {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 400;
            color: #ecd9a4;
            margin-bottom: 12px;
        }
        .gold-divider {
            width: 50px;
            height: 1px;
            background: #d4af37;
            margin: 16px 0 20px;
        }
        .welcome-message {
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            line-height: 1.7;
            color: #c4b89a;
            margin-bottom: 24px;
        }

        /* CREDENTIALS CARD (elevated) */
        .credentials-card {
            background: #111014;
            border-radius: 20px;
            border: 1px solid rgba(212,175,55,0.25);
            padding: 28px 32px;
            margin: 16px 0 28px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }
        .credentials-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 500;
            color: #d4af37;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }
        .credential-row {
            background: #0a0a0e;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 14px;
            border-left: 3px solid #d4af37;
        }
        .credential-label {
            font-family: 'Jost', sans-serif;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #b88c3c;
            margin-bottom: 6px;
        }
        .credential-value {
            font-family: 'Jost', sans-serif;
            font-size: 16px;
            font-weight: 500;
            color: #f5e8c0;
            word-break: break-all;
        }
        .password-note {
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            color: #9e9284;
            margin-top: 16px;
            text-align: center;
            padding: 12px;
            background: rgba(212,175,55,0.08);
            border-radius: 12px;
        }
        .cta-btn {
            display: inline-block;
            padding: 14px 36px;
            font-family: 'Jost', sans-serif;
            font-weight: 400;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #0d0c0b;
            background: #b88c3c;
            text-decoration: none;
            transition: background 0.2s ease;
            border-radius: 40px;
            margin-top: 24px;
        }
        .cta-btn:hover {
            background: #d4a84b;
        }

        /* FEATURE ROWS */
        .features {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 40px 0 28px;
        }
        .feature-col {
            display: table-cell;
            width: 33.33%;
            padding: 28px 20px;
            text-align: center;
            vertical-align: top;
            border-top: 1px solid #2a2318;
        }
        .feature-col:not(:last-child) {
            border-right: 1px solid #1c1913;
        }
        .feature-icon {
            font-size: 22px;
            color: #b88c3c;
            margin-bottom: 12px;
            line-height: 1;
        }
        .feature-title {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 500;
            font-size: 16px;
            color: #e8dfd0;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .feature-desc {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 12px;
            line-height: 1.8;
            color: #6e6256;
        }

        /* SECONDARY CTA */
        .secondary-cta {
            text-align: center;
            padding: 32px 0 16px;
        }
        .secondary-cta a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #b88c3c;
            text-decoration: none;
            border-bottom: 1px solid #3a2e18;
            padding-bottom: 3px;
        }

        /* QUOTE BLOCK */
        .quote-block {
            padding: 44px 32px;
            text-align: center;
            border-top: 1px solid rgba(212,175,55,0.12);
            border-bottom: 1px solid rgba(212,175,55,0.12);
            margin: 36px 0 24px;
            background: linear-gradient(135deg, #0c0b07 0%, #12100a 50%, #0c0b07 100%);
        }
        .quote-mark {
            font-family: 'Cormorant Garamond', serif;
            font-size: 68px;
            color: rgba(212,175,55,0.15);
            line-height: 0.5;
            margin-bottom: 20px;
        }
        .quote-text {
            margin: 0 0 20px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px;
            font-weight: 300;
            font-style: italic;
            color: #f5e8c0;
            line-height: 1.65;
        }
        .quote-author {
            margin: 0;
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            font-weight: 400;
            color: rgba(212,175,55,0.6);
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        /* FOOTER BRAND */
        .footer {
            background: #090807;
            padding: 40px 48px 36px;
            border-top: 1px solid #1c1913;
            text-align: center;
        }
        .footer-logo {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 22px;
            letter-spacing: 8px;
            text-transform: uppercase;
            color: #5a4e3a;
            margin-bottom: 28px;
        }
        .footer-links {
            margin-bottom: 28px;
        }
        .footer-links a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #4a4033;
            text-decoration: none;
            margin: 0 14px;
        }
        .footer-links a:hover {
            color: #b88c3c;
        }
        .footer-separator {
            width: 32px;
            height: 1px;
            background: #2a2318;
            margin: 0 auto 24px;
        }
        .footer-address {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 11px;
            line-height: 1.9;
            color: #3a3020;
            letter-spacing: 0.5px;
        }
        .footer-address a {
            color: #4a4033;
            text-decoration: none;
        }
        .footer-unsubscribe {
            margin-top: 20px;
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            color: #2e2820;
            letter-spacing: 1px;
        }
        .footer-unsubscribe a {
            color: #3a3020;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @php
        /*
        |--------------------------------------------------------------------------
        | ACCOUNT CREDENTIALS DATA
        |--------------------------------------------------------------------------
        | This template is used for sending account credentials to newly created users.
        | Variables expected: $user (object with full_name, email), $accountType (string),
        | $plainPassword (string - temporary password)
        */
        $userFullName = $user->full_name ?? ($user->name ?? 'Valued Customer');
        $userEmail = $user->email;
        $accountTypeLabel = ucfirst($accountType ?? 'Account');
    @endphp

    <div class="email-wrapper">
        <!-- HEADER (brand consistent) -->
        <div class="header">
            <a href="https://www.markupdesigns.net/morovski-light-web/">
                <img src="https://www.markupdesigns.net/morovski-light/logo/MORVOSKI-logo.png" alt="MOROVSKI" class="logo-img" />
            </a>
            <p class="tagline">Crafted Illumination</p>
        </div>

        <!-- HERO BANNER -->
        <div class="hero">
            <div class="hero-eyebrow">WELCOME TO MOROVSKI</div>
            <div class="hero-title">
                Your {{ $accountTypeLabel }} <em>✦</em>
            </div>
            <div class="hero-body">
                ✦ Your {{ $accountTypeLabel }} has been created with grace. Below are your secure credentials to access the Morovski universe. ✦
            </div>
        </div>

        <!-- CORE CONTENT -->
        <div class="content">
            <!-- WELCOME CARD -->
            <div class="welcome-card">
                <div class="welcome-salute">
                    Dear <strong style="font-weight:500; color:#f5e8c0;">{{ $userFullName }}</strong>
                </div>
                <div class="gold-divider"></div>
                <div class="welcome-message">
                    We are honored to welcome you to the Morovski inner circle. Your {{ strtolower($accountTypeLabel) }} has been meticulously prepared, granting you access to exclusive collections, personalized lighting consultations, and the artisan world of crafted illumination.
                </div>
            </div>

            <!-- CREDENTIALS CARD (secure info) -->
            <div class="credentials-card">
                <div class="credentials-title">✦ ACCESS CREDENTIALS ✦</div>
                
                <div class="credential-row">
                    <div class="credential-label">EMAIL ADDRESS</div>
                    <div class="credential-value">{{ $userEmail }}</div>
                </div>
                
                <div class="credential-row">
                    <div class="credential-label">TEMPORARY PASSWORD</div>
                    <div class="credential-value">{{ $plainPassword }}</div>
                </div>
                
                <div class="password-note">
                    🔐 For security, please log in and change your password immediately.
                </div>
                
                <div style="text-align: center;">
                    <a href="https://www.markupdesigns.net/morovski-light-web/login" class="cta-btn">ACCESS MY ACCOUNT →</a>
                </div>
            </div>

            <!-- FEATURE ROWS (brand essence) -->
            <div class="features">
                <div class="feature-col">
                    <div class="feature-icon">✦</div>
                    <div class="feature-title">Artisan Craft</div>
                    <div class="feature-desc">Each piece meticulously hand-finished by our lighting artisans.</div>
                </div>
                <div class="feature-col">
                    <div class="feature-icon">◈</div>
                    <div class="feature-title">Timeless Design</div>
                    <div class="feature-desc">Silhouettes conceived to endure beyond the transience of trend.</div>
                </div>
                <div class="feature-col">
                    <div class="feature-icon">⌘</div>
                    <div class="feature-title">Smart Light</div>
                    <div class="feature-desc">Seamless integration with modern intelligent home systems.</div>
                </div>
            </div>

            <!-- LUXURY QUOTE BLOCK -->
            <div class="quote-block">
                <div class="quote-mark">&ldquo;</div>
                <div class="quote-text">Light is not merely something to see by — it is something to see with. Morovski made us understand the difference.</div>
                <div class="quote-author">— Elana V., Interior Architect, Milan</div>
            </div>

            <!-- SECONDARY CTA -->
            <div class="secondary-cta">
                <a href="https://www.markupdesigns.net/morovski-light-web/">Explore Morovski.com →</a>
            </div>
        </div>

        <!-- FOOTER (brand aligned) -->
        <!--<div class="footer">-->
        <!--    <div class="footer-logo">MOROVSKI</div>-->
        <!--    <div class="footer-links">-->
        <!--        <a href="https://www.markupdesigns.net/morovski-light-web/terms-and-condition">Terms &amp; Conditions</a>-->
        <!--        <a href="https://www.markupdesigns.net/morovski-light-web/privacy-policy">Privacy Policy</a>-->
        <!--        <a href="https://www.markupdesigns.net/morovski-light-web/cookies-policy">Cookies Policy</a>-->
        <!--    </div>-->
        <!--    <div class="footer-separator"></div>-->
        <!--    <div class="footer-address">-->
        <!--        MOROVSKI Lighting Co. &nbsp;·&nbsp; Studio &amp; Showroom<br />-->
        <!--        You are receiving this because your {{ strtolower($accountTypeLabel) }} was created at Morovski.<br />-->
        <!--        <a href="https://www.markupdesigns.net/morovski-light-web/">morovski-light-web.com</a>-->
        <!--    </div>-->
           
        <!--</div>-->
    </div>
</body>
</html>