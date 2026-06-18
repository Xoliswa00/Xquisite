<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — Xquisite Creations</title>
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
        <h1 class="f-mont text-3xl sm:text-4xl font-bold text-[#002B5B] mb-3">Privacy Policy</h1>
        <p class="text-sm text-[#2D3748]/50">Last updated: {{ now()->format('d F Y') }} &middot; Xquisite Creations (Pty) Ltd</p>
    </div>

    <div class="prose space-y-2">

        <p>
            This Privacy Policy explains how <strong>Xquisite Creations (Pty) Ltd</strong> ("we", "us", "our")
            collects, uses, and protects personal information in accordance with the
            <strong>Protection of Personal Information Act 4 of 2013 (POPIA)</strong> and
            other applicable South African law.
        </p>

        <h2>1. Information We Collect</h2>
        <p>We collect personal information in the following categories:</p>
        <ul>
            <li><strong>Account information:</strong> your name, email address, phone number, and business details provided at registration.</li>
            <li><strong>Business data you upload:</strong> client records, appointment details, transaction history, product catalogues, and any other data you enter into the Platform.</li>
            <li><strong>Usage data:</strong> pages visited, features used, login timestamps, and device/browser information — collected to improve the Platform.</li>
            <li><strong>Payment information:</strong> billing amounts and payment status. Card details are processed directly by PayFast and are never stored on our servers.</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <ul>
            <li>To provide, maintain, and improve the Platform and its features.</li>
            <li>To process your subscription and send billing-related communications.</li>
            <li>To send you service announcements, security alerts, and support messages.</li>
            <li>To comply with legal and regulatory obligations.</li>
            <li>To detect, prevent, and investigate fraud or misuse of the Platform.</li>
        </ul>
        <p>We do not sell, rent, or trade your personal information to third parties for marketing purposes.</p>

        <h2>3. Your Business Data</h2>
        <p>
            Data you upload — including your clients' personal information — is processed by us as an
            operator on your behalf under POPIA. You remain the responsible party for that data. You
            are responsible for ensuring you have lawful grounds to upload and process your clients'
            personal information through the Platform.
        </p>

        <h2>4. Data Sharing &amp; Third Parties</h2>
        <p>We share personal information only in these limited circumstances:</p>
        <ul>
            <li><strong>PayFast</strong> — for payment processing. Subject to PayFast's own Privacy Policy.</li>
            <li><strong>Cloud infrastructure providers</strong> — for hosting and data storage. All providers are contractually bound to protect your data.</li>
            <li><strong>Legal requirements</strong> — where disclosure is required by South African law, court order, or regulatory authority.</li>
        </ul>

        <h2>5. Data Security</h2>
        <p>
            We implement appropriate technical and organisational measures to protect personal information
            against loss, unauthorised access, disclosure, or alteration. These include encrypted connections
            (HTTPS), access controls, and regular security reviews. However, no system is completely secure
            and we cannot guarantee absolute security.
        </p>

        <h2>6. Data Retention</h2>
        <p>
            We retain your account and business data for as long as your account remains active, plus a
            further period as required by applicable law (typically 5 years for financial records).
            Upon account closure, you may request an export of your data within 30 days.
            After that period, data is securely deleted.
        </p>

        <h2>7. Your Rights Under POPIA</h2>
        <p>You have the right to:</p>
        <ul>
            <li>Request confirmation of whether we hold personal information about you.</li>
            <li>Request correction of inaccurate or incomplete personal information.</li>
            <li>Request deletion of your personal information (subject to legal retention obligations).</li>
            <li>Object to the processing of your personal information.</li>
            <li>Lodge a complaint with the Information Regulator of South Africa.</li>
        </ul>
        <p>
            To exercise any of these rights, contact our Information Officer at
            <a href="mailto:privacy@xquisitecreations.co.za">privacy@xquisitecreations.co.za</a>.
        </p>

        <h2>8. Cookies</h2>
        <p>
            The Platform uses essential session cookies to keep you logged in and to protect against
            cross-site request forgery. We do not use third-party advertising or tracking cookies.
        </p>

        <h2>9. Children</h2>
        <p>
            The Platform is not directed at children under the age of 18. We do not knowingly collect
            personal information from minors. If you believe a minor has provided us with personal
            information, please contact us so we can remove it.
        </p>

        <h2>10. Changes to This Policy</h2>
        <p>
            We may update this Privacy Policy from time to time. We will notify registered users by email
            at least 14 days before material changes take effect. The "Last updated" date at the top of
            this page reflects the most recent revision.
        </p>

        <h2>11. Contact &amp; Information Officer</h2>
        <p>
            For privacy-related queries, data access requests, or complaints, contact our Information Officer:<br>
            <a href="mailto:privacy@xquisitecreations.co.za">privacy@xquisitecreations.co.za</a><br>
            Xquisite Creations (Pty) Ltd, South Africa.
        </p>

    </div>

    <div class="mt-12 pt-8 border-t border-gray-100 flex flex-wrap gap-4 text-sm">
        <a href="/" class="text-[#0078D4] hover:text-[#0065B8]">&larr; Back to home</a>
        <a href="{{ route('terms') }}" class="text-[#0078D4] hover:text-[#0065B8]">Terms of Service &rarr;</a>
    </div>
</main>

<footer class="border-t border-gray-100 py-6 text-center text-xs text-[#2D3748]/40">
    &copy; {{ date('Y') }} Xquisite Creations (Pty) Ltd. All rights reserved.
</footer>

</body>
</html>
