<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('monitoring.index') }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-sm mb-2 inline-block">← Back to Monitoring</a>
                <h2 class="text-2xl font-bold text-[#D4AF37]">{{ $instance->name }}</h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('monitoring.edit', $instance) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Current Status Card --}}
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-lg {{ $instance->is_healthy ? 'bg-emerald-500/20 border-emerald-500/30' : 'bg-red-500/20 border-red-500/30' }} border flex items-center justify-center">
                        <svg class="w-8 h-8 {{ $instance->is_healthy ? 'text-emerald-400' : 'text-red-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($instance->is_healthy)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <p class="text-slate-400 text-sm">Status</p>
                        <p class="text-2xl font-bold {{ $instance->is_healthy ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $instance->is_healthy ? 'Healthy' : 'Unhealthy' }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-slate-400 text-sm mb-2">Uptime</p>
                    <p class="text-2xl font-bold text-white">
                        @if($instance->uptime_percentage !== null)
                            {{ number_format($instance->uptime_percentage, 1) }}%
                        @else
                            —
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 mt-1">(Last 30 days)</p>
                </div>

                <div>
                    <p class="text-slate-400 text-sm mb-2">Response Time</p>
                    <p class="text-2xl font-bold text-white">
                        @if($lastCheck?->response_time_ms)
                            {{ $lastCheck->response_time_ms }}ms
                        @else
                            —
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Latest</p>
                </div>

                <div>
                    <p class="text-slate-400 text-sm mb-2">Last Check</p>
                    <p class="text-lg font-semibold text-white">
                        @if($lastCheck)
                            {{ $lastCheck->created_at->format('H:i') }}
                        @else
                            —
                        @endif
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        @if($lastCheck)
                            {{ $lastCheck->created_at->diffForHumans() }}
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-slate-400 text-sm mb-2">Version</p>
                    <p class="text-lg font-mono font-semibold text-slate-300">
                        {{ $lastCheck?->version ?? '—' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">App version</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <h3 class="text-lg font-semibold text-[#D4AF37] mb-4">Instance Details</h3>
                <dl class="space-y-4">
                    <div class="flex justify-between items-start">
                        <dt class="text-slate-400">URL</dt>
                        <dd class="text-slate-300 text-sm break-all max-w-xs text-right">{{ $instance->url }}</dd>
                    </div>
                    <div class="flex justify-between items-start">
                        <dt class="text-slate-400">Tenant ID</dt>
                        <dd class="text-slate-300 font-mono">{{ $instance->tenant_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between items-start">
                        <dt class="text-slate-400">Status</dt>
                        <dd class="text-slate-300">
                            @if($instance->active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-900/30 text-emerald-300 border border-emerald-700/30">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-300 border border-slate-600">Inactive</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between items-start">
                        <dt class="text-slate-400">Registered</dt>
                        <dd class="text-slate-300">{{ $instance->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <h3 class="text-lg font-semibold text-[#D4AF37] mb-4">Latest Health Metrics</h3>
                @if($lastCheck)
                    <dl class="space-y-4">
                        <div class="flex justify-between items-start">
                            <dt class="text-slate-400">DB Connection</dt>
                            <dd class="text-slate-300">
                                @if($lastCheck->db_connection)
                                    <span class="inline-flex items-center gap-1.5 text-emerald-400 text-sm">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>Connected
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-red-400 text-sm">
                                        <span class="w-2 h-2 rounded-full bg-red-400"></span>Disconnected
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between items-start">
                            <dt class="text-slate-400">Queue Status</dt>
                            <dd class="text-slate-300">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $lastCheck->queue_status === 'running' ? 'bg-emerald-900/30 text-emerald-300' : 'bg-red-900/30 text-red-300' }}">
                                    {{ ucfirst($lastCheck->queue_status ?? 'unknown') }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between items-start">
                            <dt class="text-slate-400">Last Error</dt>
                            <dd class="text-slate-400 text-sm max-w-xs text-right break-all">
                                {{ $lastCheck->last_error ?? 'None' }}
                            </dd>
                        </div>
                        <div class="flex justify-between items-start">
                            <dt class="text-slate-400">Checked At</dt>
                            <dd class="text-slate-300">{{ $lastCheck->created_at->format('M d, Y H:i:s') }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-slate-400 text-sm">No health check data available yet.</p>
                @endif
            </div>
        </div>

        {{-- Health Check Timeline --}}
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <h3 class="text-lg font-semibold text-[#D4AF37] mb-6">Recent Health Checks (Last 20)</h3>
            
            @if($healthLogs->count() > 0)
                <div class="space-y-3">
                    @foreach($healthLogs as $log)
                        <div class="flex items-start gap-4 pb-4 border-b border-slate-700 last:border-0">
                            <div class="w-3 h-3 rounded-full mt-1.5 shrink-0 {{ $log->status === 'healthy' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-white font-medium">
                                            {{ ucfirst($log->status) }}
                                        </p>
                                        <p class="text-xs text-slate-400 mt-1">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-slate-400">{{ $log->response_time_ms }}ms</p>
                                        @if($log->last_error)
                                            <p class="text-xs text-red-400 mt-1">{{ Str::limit($log->last_error, 50) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination info --}}
                <div class="mt-6 pt-4 border-t border-slate-700">
                    <p class="text-xs text-slate-400">Showing 20 most recent checks. <a href="#" class="text-[#0078D4] hover:text-[#B8D4F0]">View all →</a></p>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-slate-400">No health checks recorded yet.</p>
                </div>
            @endif
        </div>

        {{-- Alerts --}}
        @if($alerts->count() > 0)
            <div class="bg-slate-800 rounded-xl p-6 border border-red-700/30">
                <h3 class="text-lg font-semibold text-[#D4AF37] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Recent Alerts
                </h3>
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="flex items-start gap-4 p-4 bg-red-900/20 rounded-lg border border-red-700/30">
                            <div class="w-2 h-2 rounded-full bg-red-500 mt-2"></div>
                            <div class="flex-1">
                                <p class="text-white font-medium">{{ ucfirst($alert->type) }}</p>
                                <p class="text-sm text-slate-400 mt-1">{{ $alert->message }}</p>
                                <p class="text-xs text-slate-500 mt-2">
                                    Occurred: {{ $alert->created_at->format('M d, Y H:i:s') }}
                                    @if($alert->resolved_at)
                                        • Resolved: {{ $alert->resolved_at->format('M d, Y H:i:s') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
