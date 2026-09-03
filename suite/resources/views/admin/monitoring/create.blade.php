<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('monitoring.index') }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-sm mb-2 inline-block">← Back to Monitoring</a>
        <h2 class="text-2xl font-bold text-[#D4AF37]">Add Monitored Instance</h2>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <form action="{{ route('monitoring.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Instance Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           placeholder="e.g., Billing App, CRM Service"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('name') border-red-500 @enderror"
                           required>
                    @error('name')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-slate-300 mb-2">Slug</label>
                    <input type="text"
                           id="slug"
                           name="slug"
                           value="{{ old('slug') }}"
                           placeholder="e.g., keystone, bx-eventos"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('slug') border-red-500 @enderror"
                           required>
                    @error('slug')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Stable id written onto every forwarded log row (system_logs.source). Lowercase, letters/numbers/dashes.</p>
                </div>

                <div>
                    <label for="url" class="block text-sm font-medium text-slate-300 mb-2">Instance URL</label>
                    <input type="url" 
                           id="url" 
                           name="url" 
                           value="{{ old('url') }}"
                           placeholder="https://example.com"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('url') border-red-500 @enderror"
                           required>
                    @error('url')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">The base URL where this app instance is hosted</p>
                </div>

                <div>
                    <label for="api_token" class="block text-sm font-medium text-slate-300 mb-2">API Token</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               id="api_token" 
                               name="api_token" 
                               value="{{ old('api_token') }}"
                               placeholder="64-character random token"
                               class="flex-1 px-4 py-2 bg-slate-700 border border-slate-600 text-white placeholder-slate-500 rounded-lg font-mono text-sm focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4] @error('api_token') border-red-500 @enderror"
                               required>
                        <button type="button" 
                                onclick="generateToken()"
                                class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg transition font-medium text-sm">
                            Generate
                        </button>
                    </div>
                    @error('api_token')
                        <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Secure token used by the remote app to authenticate health reports</p>
                </div>

                <div>
                    <label for="tenant_id" class="block text-sm font-medium text-slate-300 mb-2">Tenant ID (Optional)</label>
                    <input type="text" 
                           id="tenant_id" 
                           name="tenant_id" 
                           value="{{ old('tenant_id') }}"
                           placeholder="e.g., tenant_123"
                           class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white placeholder-slate-500 rounded-lg focus:outline-none focus:border-[#0078D4] focus:ring-1 focus:ring-[#0078D4]">
                    <p class="text-xs text-slate-400 mt-1">Associate this instance with a specific tenant</p>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" 
                           id="active" 
                           name="active" 
                           value="1"
                           {{ old('active', true) ? 'checked' : '' }}
                           class="w-4 h-4 bg-slate-700 border-slate-600 rounded focus:ring-[#0078D4]">
                    <label for="active" class="text-sm text-slate-300">Actively monitor this instance</label>
                </div>

                {{-- Integration Instructions --}}
                <div class="bg-[#001A3A]/20 border border-[#002B5B]/30 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-[#B8D4F0] mb-3">Integration Steps</h4>
                    <ol class="space-y-2 text-xs text-slate-300">
                        <li><strong>1.</strong> Give the remote app the Slug and API Token above as <code class="bg-slate-900 px-1.5 py-0.5 rounded text-[#B8D4F0]">MONITORING_TOKEN</code></li>
                        <li><strong>2.</strong> Remote app pushes error+ logs to: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-[#B8D4F0]">{{ config('app.url') }}/ingest/logs</code> — they show at <code class="bg-slate-900 px-1.5 py-0.5 rounded text-[#B8D4F0]">/admin/logs?source={{ '{slug}' }}</code></li>
                        <li><strong>3.</strong> Remote app pushes health status to: <code class="bg-slate-900 px-1.5 py-0.5 rounded text-[#B8D4F0]">{{ config('app.url') }}/api/health-report</code></li>
                        <li><strong>4.</strong> This platform also pulls <code class="bg-slate-900 px-1.5 py-0.5 rounded text-[#B8D4F0]">{url}/api/health</code> every 5 minutes as a backstop — see docs/MONITORING_INGEST.md for the full contract of both paths</li>
                    </ol>
                </div>

                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg font-medium transition">
                        Create Instance
                    </button>
                    <a href="{{ route('monitoring.index') }}"
                       class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg font-medium transition text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Example Configuration --}}
        <div class="mt-8 bg-slate-800 rounded-xl p-6 border border-slate-700">
            <h3 class="text-lg font-semibold text-[#D4AF37] mb-4">How to Configure Remote App</h3>
            <p class="text-slate-400 text-sm mb-4">Every reporter app just needs these four env vars pointed at this hub — nothing else changes per project:</p>
            <pre class="bg-slate-900 p-4 rounded-lg overflow-x-auto text-xs text-slate-300"><code>// .env
MONITORING_ENABLED=true
MONITORING_URL={{ config('app.url') }}
MONITORING_TOKEN=&lt;the API Token above&gt;
MONITORING_SLUG=&lt;the Slug above&gt;

// config/services.php
'monitoring' => [
    'enabled' => env('MONITORING_ENABLED', false),
    'url'     => env('MONITORING_URL'),   // this hub's base URL
    'token'   => env('MONITORING_TOKEN'), // this instance's api_token
    'slug'    => env('MONITORING_SLUG'),  // must match the Slug set here
],

// Schedule in routes/console.php (or app/Console/Kernel.php)
$schedule->job(new \App\Jobs\ReportHealthStatus)->everyFiveMinutes();   // POST {url}/api/health-report
$schedule->job(new \App\Jobs\ForwardErrorLogs)->everyFiveMinutes();     // POST {url}/ingest/logs (batched, see docs/MONITORING_INGEST.md)</code></pre>
        </div>
    </div>

    <script>
        function generateToken() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let token = '';
            for (let i = 0; i < 64; i++) {
                token += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('api_token').value = token;
        }
    </script>
</x-app-layout>
