<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Morovski Lights | Shared Products</title>
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
            max-width: 560px;
            margin: 0 auto;
            background-color: #0d0c0b;
            width: 100%;
        }
        /* RESPONSIVE */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                max-width: 100%;
            }
            .content, .hero, .footer {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .links-card {
                padding: 24px 20px !important;
            }
            .hero-title {
                font-size: 28px !important;
            }
            .link-item {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 8px !important;
            }
            .link-url {
                word-break: break-all;
            }
        }

        /* HEADER STYLES */
        .header {
            background: linear-gradient(180deg, #0d0c0b 0%, #13110e 100%);
            padding: 24px 24px 22px;
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
            width: 280px;
            height: 160px;
            background: radial-gradient(ellipse at center, rgba(184,140,60,0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .logo-img {
            max-width: 100px;
            border-radius: 50%;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: brightness(1.05);
        }
        .tagline {
            font-family: 'Jost', sans-serif;
            font-weight: 200;
            font-size: 9px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-top: 8px;
        }

        /* HERO BANNER - MINIMAL */
        .hero {
            background: #13110e;
            padding: 36px 32px 32px;
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
            width: 50px;
            height: 1px;
            background: #b88c3c;
        }
        .hero-eyebrow {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 9px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #b88c3c;
            margin-bottom: 12px;
        }
        .hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 32px;
            line-height: 1.2;
            color: #f0e8d8;
            margin-bottom: 8px;
        }
        .hero-title em {
            font-style: italic;
            color: #d4a84b;
        }

        /* CONTENT CORE */
        .content {
            background: #0d0c0b;
            padding: 0 32px 40px;
        }

        /* SHARED LINKS CARD */
        .links-card {
            background: rgba(19, 17, 14, 0.95);
            border-radius: 24px;
            border: 0.5px solid rgba(184,140,60,0.4);
            padding: 28px 28px;
            margin: 32px 0 24px;
        }
        .card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 400;
            color: #ecd9a4;
            text-align: center;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }
        .card-subtitle {
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            color: #9e9284;
            text-align: center;
            margin-bottom: 24px;
        }
        
        /* LINKS LIST */
        .links-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            background: #0a0a0e;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 10px;
            border-left: 3px solid #b88c3c;
            transition: all 0.2s ease;
        }
        .link-icon {
            font-size: 16px;
            color: #b88c3c;
            flex-shrink: 0;
        }
        .link-url {
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            color: #c4b89a;
            text-decoration: none;
            flex: 1;
            word-break: break-all;
            line-height: 1.4;
        }
        .link-url:hover {
            color: #d4af37;
            text-decoration: underline;
        }
        .view-btn {
            display: inline-block;
            padding: 6px 14px;
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #0d0c0b;
            background: #b88c3c;
            text-decoration: none;
            border-radius: 30px;
            white-space: nowrap;
            transition: background 0.2s ease;
        }
        .view-btn:hover {
            background: #d4a84b;
        }
        
        /* MINI FEATURE LINE */
        .mini-feature {
            text-align: center;
            padding: 20px 0 16px;
            border-top: 1px solid rgba(212,175,55,0.12);
            margin: 8px 0 12px;
        }
        .mini-feature p {
            font-family: 'Jost', sans-serif;
            font-size: 10px;
            letter-spacing: 2px;
            color: #6e6256;
        }
        .mini-feature span {
            color: #b88c3c;
            margin: 0 5px;
        }

        /* SECONDARY LINK */
        .secondary-link {
            text-align: center;
            padding: 16px 0 8px;
        }
        .secondary-link a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #b88c3c;
            text-decoration: none;
            border-bottom: 1px solid #3a2e18;
            padding-bottom: 3px;
        }

        /* FOOTER - MINIMAL */
        .footer {
            background: #090807;
            padding: 28px 32px 24px;
            border-top: 1px solid #1c1913;
            text-align: center;
        }
        .footer-logo {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 300;
            font-size: 18px;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: #5a4e3a;
            margin-bottom: 20px;
        }
        .footer-links {
            margin-bottom: 20px;
        }
        .footer-links a {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #4a4033;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer-separator {
            width: 28px;
            height: 1px;
            background: #2a2318;
            margin: 0 auto 18px;
        }
        .footer-address {
            font-family: 'Jost', sans-serif;
            font-weight: 300;
            font-size: 10px;
            line-height: 1.7;
            color: #3a3020;
        }
        .footer-unsubscribe {
            margin-top: 14px;
            font-family: 'Jost', sans-serif;
            font-size: 9px;
            color: #2e2820;
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
        | SHARED PRODUCT LINKS
        |--------------------------------------------------------------------------
        | Variables expected: $links (array of product URLs)
        */
    @endphp

    <div class="email-wrapper">
        <!-- HEADER -->
        <div class="header">
            <a href="https://www.markupdesigns.net/morovski-light-web/">
                <img src="https://www.markupdesigns.net/morovski-light/logo/MORVOSKI-logo.png" alt="MOROVSKI" class="logo-img" />
            </a>
            <p class="tagline">Crafted Illumination</p>
        </div>

        <!-- HERO - MINIMAL -->
        <div class="hero">
            <div class="hero-eyebrow">✦ CURATED FOR YOU ✦</div>
            <div class="hero-title">
                Shared <em>Products</em>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <!-- LINKS CARD -->
            <div class="links-card">
                <div class="card-title">✦ Illuminated Selections ✦</div>
                <div class="card-subtitle">Click below to view each piece</div>
                
                <ul class="links-list">
                    @foreach($links as $link)
                        <li class="link-item">
                            <span class="link-icon">◈</span>
                            <a href="{{ $link }}" class="link-url">{{ $link }}</a>
                            <a href="{{ $link }}" class="view-btn">VIEW</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- MINI FEATURE LINE -->
            <div class="mini-feature">
                <p><span>✦</span> Artisan Crafted <span>✦</span> Timeless Design <span>✦</span> Smart Light Ready <span>✦</span></p>
            </div>

            <!-- SECONDARY LINK -->
            <div class="secondary-link">
                <a href="https://www.markupdesigns.net/morovski-light-web/">Explore All Collections →</a>
            </div>
        </div>

        <!-- FOOTER - MINIMAL -->
        <div class="footer">
            <div class="footer-logo">MOROVSKI</div>
            <div class="footer-links">
                <a href="https://www.markupdesigns.net/morovski-light-web/terms-and-condition">Terms</a>
                <a href="https://www.markupdesigns.net/morovski-light-web/privacy-policy">Privacy</a>
                <a href="https://www.markupdesigns.net/morovski-light-web/cookies-policy">Cookies</a>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-address">
                MOROVSKI Lighting Co. · Studio & Showroom
            </div>
            <div class="footer-unsubscribe">
                <a href="#">Unsubscribe</a> &nbsp;·&nbsp;
                <a href="#">Preferences</a>
            </div>
        </div>
    </div>
</body>
</html>