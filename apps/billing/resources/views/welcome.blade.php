<x-guest-layout>

    @push('styles')
    <style>
        :root {
            --brand-gold: #D4AF37;
            --deep-navy: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        .hero-gradient {
            background: radial-gradient(circle at 0% 0%, #1e293b 0%, #0f172a 100%);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(100px);
            border: 1px solid rgba(212, 175, 55, 0.25);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }
    </style>
    @endpush


    {{-- HERO SECTION --}}
    <section class="relative min-h-[85vh] bg-[#0f172a] flex items-center pt-20 pb-16">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 w-full">
            <div class="grid lg:grid-cols-2 gap-14 items-center">

                {{-- LEFT --}}
                <div class="space-y-10">

                    <div>
                        <h1 class="text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                            Custom <span class="text-brand-gold">Business Systems</span><br>
                            Built To Scale Operations
                        </h1>

                        <p class="text-xl text-slate-400 mt-6 max-w-xl leading-relaxed">
                            We design and develop custom platforms that eliminate manual processes,
                            connect fragmented data, and give business owners real operational control.
                        </p>
                    </div>


             <div class="grid sm:grid-cols-2 gap-4">

    <div class="flex items-center gap-3 text-slate-200 bg-white/5 p-3 rounded-lg border border-white/10">
        <span class="text-brand-gold font-bold">01</span>
        <span class="text-sm font-semibold uppercase tracking-wide">Logistics ERP Platforms</span>
    </div>

    <div class="flex items-center gap-3 text-slate-200 bg-white/5 p-3 rounded-lg border border-white/10">
        <span class="text-brand-gold font-bold">02</span>
        <span class="text-sm font-semibold uppercase tracking-wide">Retail POS & Inventory Systems</span>
    </div>

    <div class="flex items-center gap-3 text-slate-200 bg-white/5 p-3 rounded-lg border border-white/10">
        <span class="text-brand-gold font-bold">03</span>
        <span class="text-sm font-semibold uppercase tracking-wide">Debt Collection Automation Tools</span>
    </div>

    <div class="flex items-center gap-3 text-slate-200 bg-white/5 p-3 rounded-lg border border-white/10">
        <span class="text-brand-gold font-bold">04</span>
        <span class="text-sm font-semibold uppercase tracking-wide">Business Intelligence Dashboards</span>
    </div>

</div>


                    <div class="flex flex-col sm:flex-row gap-4">

                        <a href="#contact"
                            class="px-8 py-4 bg-brand-gold text-slate-900 font-bold rounded hover:brightness-110 transition-all text-center">
                            Request System Audit
                        </a>

                        <a href="#services"
                            class="px-8 py-4 border border-white/20 text-white font-bold rounded hover:bg-white/5 transition-all text-center">
                            View Solutions
                        </a>

                    </div>

                    <p class="text-sm text-slate-500">
                        Secure cloud architecture • Modern development stacks • Scalable infrastructure
                    </p>

                </div>


                {{-- RIGHT --}}
         <div class="bg-slate-800/50 p-8 rounded-2xl border border-white/10 relative shadow-lg">

    {{-- Founder --}}
    <div class="flex items-center gap-4 mb-8 pb-6 border-b border-white/10">
        <img src="{{ asset('assets/images/asset_3.png') }}"
             class="w-20 h-35 rounded-full object-cover border-2 border-brand-gold">
        <div>
                        <p class="text-white font-bold text-lg">Xoliswa Masuku</p>

            <p class="text-white font-bold text-lg">Founder</p>
            <p class="text-slate-400 text-sm">Systems Developer</p>
             Building scalable ERPs, POS systems, loan platforms, automation tools, and BI dashboards 
            to help businesses run smarter and grow faster.
       
        </div>
        
    </div>

    <h3 class="text-brand-gold text-xs font-black tracking-widest uppercase mb-6">
        Recent System Builds
    </h3>

    <div class="space-y-6">

        {{-- ERP Platforms --}}
        <div class="flex items-start gap-4 pb-6 border-b border-white/5 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/erp.jpg') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">ERP Platforms</p>
                <p class="text-slate-400 text-sm">
                    Customized ERP solutions to automate core business operations and reporting.
                </p>
            </div>
        </div>

        {{-- POS Systems --}}
        <div class="flex items-start gap-4 pb-6 border-b border-white/5 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/public.avif') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">POS Systems</p>
                <p class="text-slate-400 text-sm">
                    Integrated point-of-sale platforms with inventory and multi-location reporting.
                </p>
            </div>
        </div>

        {{-- Loan / Finance Systems --}}
        <div class="flex items-start gap-4 pb-6 border-b border-white/5 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/finance.png') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">Loan / Finance Systems</p>
                <p class="text-slate-400 text-sm">
                    End-to-end loan management and finance platforms for efficient workflows and accurate reporting.
                </p>
            </div>
        </div>

        {{-- Client Portals --}}
        <div class="flex items-start gap-4 pb-6 border-b border-white/5 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/portal.png') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">Client Portals</p>
                <p class="text-slate-400 text-sm">
                    Interactive portals to enhance client engagement and service delivery.
                </p>
            </div>
        </div>

        {{-- Automation Tools --}}
        <div class="flex items-start gap-4 pb-6 border-b border-white/5 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/automation.png') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">Automation Tools</p>
                <p class="text-slate-400 text-sm">
                    Streamlining repetitive workflows to save time and reduce errors across businesses.
                </p>
            </div>
        </div>

        {{-- Reporting Dashboards --}}
        <div class="flex items-start gap-4 group">
            <div class="w-12 h-12 bg-white/5 rounded flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:bg-brand-gold/10">
                <img src="{{ asset('assets/images/dashboard.png') }}"
                     class="w-8 h-8 object-cover rounded grayscale group-hover:grayscale-0 transition-all duration-300">
            </div>
            <div>
                <p class="text-white font-bold text-lg">Reporting Dashboards</p>
                <p class="text-slate-400 text-sm">
                    Real-time dashboards for monitoring KPIs and driving data-based decisions.
                </p>
            </div>
        </div>

    </div>

    {{-- Metrics --}}
    <div class="grid grid-cols-3 gap-4 mt-10 text-center">
        <div>
            <p class="text-white text-xl font-bold">15+</p>
            <p class="text-xs text-slate-400 uppercase">Projects</p>
        </div>
        <div>
            <p class="text-white text-xl font-bold">98%</p>
            <p class="text-xs text-slate-400 uppercase">Success</p>
        </div>
        <div>
            <p class="text-white text-xl font-bold">5+</p>
            <p class="text-xs text-slate-400 uppercase">Industries</p>
        </div>
    </div>

</div>

            </div>
        </div>

    </section>


    {{-- INDUSTRIES --}}
    <div class="bg-slate-50 py-16 border-b border-slate-200">

        <div class="max-w-7xl mx-auto px-6 text-center">

            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.3em] mb-10">
                Industries We Support
            </p>

            <div class="flex flex-wrap justify-center gap-12 text-lg font-bold text-slate-700">

                <span>Logistics</span>
                <span>Retail</span>
                <span>Finance</span>
                <span>Healthcare</span>
                <span>E-commerce</span>

            </div>

        </div>
    </div>


    {{-- PROCESS --}}
    <section class="py-24 bg-white">

        <div class="max-w-6xl mx-auto px-6">

            <h2 class="text-4xl font-bold text-center mb-16">
                Our Development Process
            </h2>

            <div class="grid md:grid-cols-4 gap-10 text-center">

                <div>
                    <h3 class="font-bold text-lg mb-2">1. Audit</h3>
                    <p class="text-slate-600 text-sm">
                        We analyse your workflows, systems and operational bottlenecks.
                    </p>
                </div>

                <div>
                    <h3 class="font-bold text-lg mb-2">2. Architecture</h3>
                    <p class="text-slate-600 text-sm">
                        A scalable system structure is designed for your business.
                    </p>
                </div>

                <div>
                    <h3 class="font-bold text-lg mb-2">3. Development</h3>
                    <p class="text-slate-600 text-sm">
                        Your custom software platform is engineered and tested.
                    </p>
                </div>

                <div>
                    <h3 class="font-bold text-lg mb-2">4. Deployment</h3>
                    <p class="text-slate-600 text-sm">
                        The system launches with monitoring and optimization.
                    </p>
                </div>

            </div>

        </div>

    </section>


    {{-- SOLUTIONS --}}
    <section id="services" class="py-28 bg-[#0f172a]">

        <div class="max-w-7xl mx-auto px-6">

            <div class="text-center mb-20">

                <span class="text-brand-gold font-bold tracking-[0.4em] uppercase text-xs">
                    Solutions
                </span>

                <h2 class="text-4xl lg:text-5xl font-bold text-white mt-4">
                    Service Packages
                </h2>

            </div>


            <div class="grid md:grid-cols-3 gap-8">


                {{-- Starter --}}
                <div class="p-10 bg-white/5 border border-white/10 rounded-3xl">

                    <h3 class="text-3xl font-bold text-white mb-4">Starter</h3>

                    <p class="text-slate-400 mb-8">
                        Professional business websites designed for credibility and conversion.
                    </p>

                    <ul class="space-y-3 mb-8 text-slate-300 text-sm">
                        <li>✔ SEO optimized</li>
                        <li>✔ Responsive design</li>
                        <li>✔ Conversion layout</li>
                    </ul>

                    <div class="text-white font-black text-2xl mb-6">
                        From R3,500
                    </div>

                </div>


                {{-- Growth --}}
                <div class="p-10 bg-white shadow-xl rounded-3xl border-4 border-brand-gold">

                    <h3 class="text-3xl font-bold text-slate-900 mb-4">Growth</h3>

                    <p class="text-slate-600 mb-8">
                        Custom dashboards, automation tools and business workflow systems.
                    </p>

                    <ul class="space-y-3 mb-8 text-slate-600 text-sm">
                        <li>✔ Client portals</li>
                        <li>✔ API integrations</li>
                        <li>✔ reporting dashboards</li>
                    </ul>

                    <div class="text-slate-900 font-black text-2xl mb-6">
                        From R12,000
                    </div>

                </div>


                {{-- Enterprise --}}
                <div class="p-10 bg-white/5 border border-white/10 rounded-3xl">

                    <h3 class="text-3xl font-bold text-white mb-4">
                        Enterprise
                    </h3>

                    <p class="text-slate-400 mb-8">
                        Custom ERP systems, loan platforms and SaaS software.
                    </p>

                    <ul class="space-y-3 mb-8 text-slate-300 text-sm">
                        <li>✔ Multi tenant architecture</li>
                        <li>✔ High security systems</li>
                        <li>✔ Custom integrations</li>
                    </ul>

                    <div class="text-white font-black text-2xl mb-6">
                        Custom Quote
                    </div>

                </div>


            </div>
        </div>

    </section>


    {{-- CTA --}}
    <section id="contact" class="py-28 bg-white text-center">

        <div class="max-w-4xl mx-auto px-6">

            <h2 class="text-5xl font-black text-slate-900 mb-8">
                Let's Build The System<br>
                Your Business Needs
            </h2>

            <p class="text-xl text-slate-600 mb-12">
                We design infrastructure that helps businesses operate faster,
                smarter and with greater control.
            </p>

            <a href="mailto:hello@xquisitecreations.co.za"
                class="inline-block px-14 py-6 bg-slate-900 text-white font-bold rounded-2xl hover:scale-105 transition-all">
                Schedule Discovery Call
            </a>

        </div>

    </section>

</x-guest-layout>