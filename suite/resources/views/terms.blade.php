<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service — Xquisite Creations</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700,800|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/img/manifest.json">
    <meta name="msapplication-TileColor" content="#002B5B">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#002B5B">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }
        .prose h2 { font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 1.125rem; color: #002B5B; margin-top: 2rem; margin-bottom: 0.5rem; }
        .prose p, .prose li { color: #2D3748; opacity: 0.8; line-height: 1.75; font-size: 0.9375rem; }
        .prose ul { list-style: disc; padding-left: 1.25rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
        .prose li { margin-bottom: 0.25rem; }
        .prose a { color: #0078D4; }
        .prose a:hover { color: #0065B8; }
    </style>
</head>
<body class="antialiased bg-white text-[#2D3748]">

{{-- NAV --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <a href="/" class="flex items-center gap-2 shrink-0">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-8 w-auto object-contain rounded-lg shrink-0">
                <div class="leading-none">
                    <p class="f-mont font-bold text-sm tracking-wide text-[#002B5B]">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-[#2D3748] hover:text-[#002B5B] transition">Log in</a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#0078D4] hover:bg-[#0065B8] rounded-lg transition-colors">Get Started</a>
            </div>
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-20">
    <div class="mb-10">
        <p class="text-xs text-[#0078D4] font-semibold uppercase tracking-widest mb-3 f-mont">Legal</p>
        <h1 class="f-mont text-3xl sm:text-4xl font-bold text-[#002B5B] mb-3">Terms of Service</h1>
        <p class="text-sm text-[#2D3748]/50">Last updated: {{ now()->format('d F Y') }} &middot; Xquisite Creations (Pty) Ltd</p>
    </div>

    <div class="prose space-y-2">

        <p>
            By registering for or using the Xquisite Creations platform ("the Platform"), you agree to be
            bound by these Terms of Service ("Terms"). Please read them carefully. If you do not agree,
            do not use the Platform.
        </p>

        <h2>1. About Us</h2>
        <p>
            The Platform is operated by <strong>Xquisite Creations (Pty) Ltd</strong>, a company registered in
            South Africa. References to "we", "us", or "our" refer to Xquisite Creations (Pty) Ltd.
        </p>

        <h2>2. Eligibility</h2>
        <p>
            You must be at least 18 years old and operating a legitimate business to use the Platform.
            By registering, you confirm that the information you provide is accurate and that you are
            authorised to enter into this agreement on behalf of your business.
        </p>

        <h2>3. Your Account</h2>
        <ul>
            <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
            <li>You are responsible for all activity that occurs under your account.</li>
            <li>Notify us immediately at <a href="mailto:{{ config('mail.from.address', 'support@xquisitecreations.co.za') }}">support@xquisitecreations.co.za</a> if you suspect unauthorised access.</li>
            <li>You may not share your account with third parties or use a single account to represent multiple unrelated businesses.</li>
        </ul>

        <h2>4. Subscription &amp; Billing</h2>
        <ul>
            <li>Module subscriptions are billed monthly or annually as chosen at the time of activation.</li>
            <li>All prices are quoted in South African Rand (ZAR) and are inclusive of applicable VAT where stated.</li>
            <li>A 14-day free trial is available on new module activations. No charge is made until the trial period ends.</li>
            <li>You may cancel a module subscription at any time. Access continues until the end of the current billing period; no partial refunds are issued.</li>
            <li>We reserve the right to change module pricing with 30 days' written notice via email.</li>
        </ul>

        <h2>5. Acceptable Use</h2>
        <p>You agree not to use the Platform to:</p>
        <ul>
            <li>Violate any applicable South African law or regulation.</li>
            <li>Process transactions for illegal goods or services.</li>
            <li>Upload content that is defamatory, fraudulent, or infringes third-party rights.</li>
            <li>Attempt to reverse-engineer, scrape, or otherwise interfere with the Platform's infrastructure.</li>
            <li>Resell or sublicense access to the Platform without our written consent.</li>
        </ul>

        <h2>6. Your Data &amp; Content</h2>
        <p>
            You retain ownership of all business data you upload to the Platform (client records, transactions,
            product catalogues, etc.). We do not sell your data to third parties. See our
            <a href="{{ route('privacy') }}">Privacy Policy</a> for full details on how we handle your data.
        </p>

        <h2>7. Platform Availability</h2>
        <p>
            We aim for high availability but do not guarantee uninterrupted service. Planned maintenance
            will be communicated in advance where reasonably possible. We are not liable for losses arising
            from temporary service outages.
        </p>

        <h2>8. Intellectual Property</h2>
        <p>
            All Platform software, design, trademarks, and content (excluding your uploaded data) remain
            the intellectual property of Xquisite Creations (Pty) Ltd. You are granted a limited,
            non-exclusive, non-transferable licence to use the Platform for your business purposes only.
        </p>

        <h2>9. Limitation of Liability</h2>
        <p>
            To the maximum extent permitted by South African law, our total liability to you for any claim
            arising from use of the Platform shall not exceed the total fees paid by you in the three months
            preceding the claim. We are not liable for any indirect, incidental, or consequential losses.
        </p>

        <h2>10. Termination</h2>
        <p>
            Either party may terminate this agreement at any time. We reserve the right to suspend or
            terminate accounts that violate these Terms without notice. Upon termination, you may request
            an export of your data within 30 days.
        </p>

        <h2>11. Governing Law</h2>
        <p>
            These Terms are governed by and construed in accordance with the laws of the Republic of
            South Africa. Any disputes shall be subject to the jurisdiction of the South African courts.
        </p>

        <h2>12. Changes to These Terms</h2>
        <p>
            We may update these Terms from time to time. We will notify registered users by email at least
            14 days before material changes take effect. Continued use of the Platform after that date
            constitutes acceptance of the updated Terms.
        </p>

        <h2>13. Contact</h2>
        <p>
            Questions about these Terms? Email us at
            <a href="mailto:legal@xquisitecreations.co.za">legal@xquisitecreations.co.za</a>.
        </p>

    </div>

    <div class="mt-12 pt-8 border-t border-gray-100 flex flex-wrap gap-4 text-sm">
        <a href="/" class="text-[#0078D4] hover:text-[#0065B8]">&larr; Back to home</a>
        <a href="{{ route('privacy') }}" class="text-[#0078D4] hover:text-[#0065B8]">Privacy Policy &rarr;</a>
    </div>
</main>

<footer class="border-t border-gray-100 py-6 text-center text-xs text-[#2D3748]/40">
    &copy; {{ date('Y') }} Xquisite Creations (Pty) Ltd. All rights reserved.
</footer>

</body>
</html>
