<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#D4AF37]">Blocked IPs</h2>
        <p class="text-slate-400 text-sm mt-1">Manage IP addresses blocked from accessing the platform</p>
    </x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">Total Blocked</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $blocked->total() }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">Permanent Blocks</p>
                <p class="text-2xl font-bold text-red-400 mt-1">{{ $blocked->getCollection()->whereNull('expires_at')->count() }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-slate-400 text-sm">Temporary Blocks</p>
                <p class="text-2xl font-bold text-yellow-400 mt-1">{{ $blocked->getCollection()->whereNotNull('expires_at')->count() }}</p>
            </div>
        </div>

        {{-- Map --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-semibold text-white">Blocked IP Locations</h3>
                <span class="text-xs text-slate-500">{{ $points->count() }} geolocated of {{ $blocked->total() }}</span>
            </div>
            @if($points->isEmpty())
                <div class="px-6 py-12 text-center text-slate-500 text-sm">
                    No locations to show{{ $blocked->isEmpty() ? '.' : ' — geolocation lookup failed or is unavailable for these IPs.' }}
                </div>
            @else
                <div id="blocked-ip-map" class="h-96 w-full"></div>
            @endif
        </div>

        {{-- Block new IP --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Block an IP Address</h3>
            <form action="{{ route('admin.blocked-ips.store') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-xs text-slate-400 mb-1">IP Address</label>
                    <input type="text" name="ip_address" placeholder="e.g. 192.168.1.1"
                           class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-lg text-sm focus:outline-none focus:border-[#0078D4] @error('ip_address') border-red-500 @enderror"
                           value="{{ old('ip_address') }}" required>
                    @error('ip_address')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs text-slate-400 mb-1">Reason</label>
                    <input type="text" name="reason" placeholder="e.g. Brute force login attempts"
                           class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-lg text-sm focus:outline-none focus:border-[#0078D4] @error('reason') border-red-500 @enderror"
                           value="{{ old('reason') }}" required>
                    @error('reason')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="w-44">
                    <label class="block text-xs text-slate-400 mb-1">Expires in (minutes, blank = permanent)</label>
                    <input type="number" name="expires_in" placeholder="e.g. 1440"
                           class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-white rounded-lg text-sm focus:outline-none focus:border-[#0078D4]"
                           value="{{ old('expires_in') }}" min="1">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition">
                    Block IP
                </button>
            </form>
        </div>

        {{-- Blocked IPs table --}}
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800">
                <h3 class="text-base font-semibold text-white">Active Blocks</h3>
                <form action="{{ route('admin.blocked-ips.purge') }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="text-xs text-slate-400 hover:text-red-400 transition"
                            onclick="return confirm('Remove all expired blocks?')">
                        Purge expired
                    </button>
                </form>
            </div>

            @if($blocked->isEmpty())
                <div class="px-6 py-12 text-center text-slate-500 text-sm">No blocked IPs.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-slate-800">
                            <tr class="text-left text-slate-400 text-xs uppercase tracking-wide">
                                <th class="px-6 py-3">IP Address</th>
                                <th class="px-6 py-3">Reason</th>
                                <th class="px-6 py-3">Blocked By</th>
                                <th class="px-6 py-3">Expires</th>
                                <th class="px-6 py-3">Blocked At</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @foreach($blocked as $entry)
                                <tr class="hover:bg-slate-800/50">
                                    <td class="px-6 py-3 font-mono text-white">{{ $entry->ip_address }}</td>
                                    <td class="px-6 py-3 text-slate-300">{{ $entry->reason }}</td>
                                    <td class="px-6 py-3 text-slate-400">
                                        {{ $entry->blockedBy?->name ?? 'System (auto)' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($entry->expires_at)
                                            <span class="{{ $entry->isExpired() ? 'text-slate-500 line-through' : 'text-yellow-400' }}">
                                                {{ $entry->expires_at->format('d M Y H:i') }}
                                            </span>
                                        @else
                                            <span class="text-red-400 font-medium">Permanent</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-slate-500 text-xs">{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        <form action="{{ route('admin.blocked-ips.destroy', $entry) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-[#0078D4] hover:text-blue-300 transition"
                                                    onclick="return confirm('Unblock {{ $entry->ip_address }}?')">
                                                Unblock
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-800">
                    {{ $blocked->links() }}
                </div>
            @endif
        </div>

    </div>

    @if($points->isNotEmpty())
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function () {
                const points = @js($points);
                const map = L.map('blocked-ip-map', { scrollWheelZoom: false });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 18,
                }).addTo(map);

                const markers = points.map(p => {
                    const marker = L.circleMarker([p.lat, p.lon], {
                        radius: 7,
                        color: p.expired ? '#64748b' : '#ef4444',
                        fillColor: p.expired ? '#64748b' : '#ef4444',
                        fillOpacity: 0.7,
                        weight: 2,
                    }).addTo(map);

                    marker.bindPopup(
                        '<strong>' + p.ip + '</strong><br>' +
                        (p.label || 'Unknown location') + '<br>' +
                        '<span style="color:#94a3b8">' + p.reason + '</span>'
                    );

                    return marker;
                });

                if (markers.length === 1) {
                    map.setView([points[0].lat, points[0].lon], 6);
                } else {
                    map.fitBounds(L.featureGroup(markers).getBounds().pad(0.3));
                }
            })();
        </script>
    @endif
</x-app-layout>
