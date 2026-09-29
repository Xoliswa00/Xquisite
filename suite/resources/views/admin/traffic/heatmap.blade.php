<x-app-layout>
    <x-slot name="header">Click Heat Map</x-slot>

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-[#D4AF37]">Click heat map</h2>
                <p class="text-slate-400 text-sm mt-1">Where people click on a page. Warmer means more clicks. Phone and desktop are shown separately because the layouts differ.</p>
            </div>
            <a href="{{ route('admin.traffic.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Back to traffic</a>
        </div>

        <form method="GET" action="{{ route('admin.traffic.heatmap') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="path" class="block text-xs font-medium text-slate-400 mb-1">Page</label>
                <select id="path" name="path" class="bg-slate-900 border-slate-700 text-white rounded-lg text-sm min-w-[14rem]">
                    @foreach($pages as $p)
                        <option value="{{ $p->path }}" @selected($p->path === $path)>{{ $p->path }} ({{ $p->clicks }} clicks)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="device" class="block text-xs font-medium text-slate-400 mb-1">Device</label>
                <select id="device" name="device" class="bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                    <option value="mobile" @selected($device === 'mobile')>Phone</option>
                    <option value="desktop" @selected($device === 'desktop')>Desktop</option>
                </select>
            </div>
            <div>
                <label for="days" class="block text-xs font-medium text-slate-400 mb-1">Period</label>
                <select id="days" name="days" class="bg-slate-900 border-slate-700 text-white rounded-lg text-sm">
                    @foreach([7, 30, 90] as $d)<option value="{{ $d }}" @selected($days === $d)>Last {{ $d }} days</option>@endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition">Show</button>
        </form>

        @if(! $path)
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-10 text-center">
                <p class="text-slate-300">No clicks recorded yet.</p>
                <p class="text-sm text-slate-400 mt-1">Clicks appear here once visitors start using the public pages.</p>
            </div>
        @else
            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 min-w-0">
                    <p class="text-xs text-slate-400 mb-2">{{ number_format($points->count()) }} clicks on {{ $path }}, {{ $device === 'mobile' ? 'on phones' : 'on desktop' }}</p>
                    <div class="overflow-x-auto rounded-xl border border-slate-700 bg-slate-900">
                        <div id="stage" class="relative mx-auto" style="width: {{ $device === 'mobile' ? '390px' : '1280px' }}; max-width: none">
                            <iframe id="page" src="{{ url($path) }}?xq_no_track=1" title="The page, as visitors see it" style="width: 100%; height: 900px; border: 0; background: #fff" scrolling="no"></iframe>
                            <canvas id="heat" class="absolute top-0 left-0 pointer-events-none" style="opacity: .75"></canvas>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    @if($reach && $reach->total > 0)
                        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                            <h3 class="text-sm font-semibold text-slate-300 mb-1">How far down they read</h3>
                            <p class="text-xs text-slate-400 mb-4">Out of {{ number_format($reach->total) }} visits.</p>
                            <dl class="space-y-2 text-sm">
                                @foreach(['q1' => 'Reached the first quarter', 'q2' => 'Reached the halfway point', 'q3' => 'Reached three quarters', 'q4' => 'Reached the bottom'] as $k => $label)
                                    <div class="flex justify-between"><dt class="text-slate-300">{{ $label }}</dt><dd class="text-white font-semibold">{{ round($reach->$k / $reach->total * 100) }}%</dd></div>
                                @endforeach
                            </dl>
                        </div>
                    @endif

                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
                        <h3 class="text-sm font-semibold text-slate-300 mb-4">What they pressed</h3>
                        <dl class="space-y-3 text-sm">
                            @forelse($elements as $e)
                                <div class="flex justify-between gap-3">
                                    <dt class="min-w-0"><span class="text-slate-200 block truncate">{{ $e->label ?: ($e->href ?: 'Unlabelled') }}</span>@if($e->href)<span class="text-xs text-slate-500 block truncate">goes to {{ $e->href }}</span>@endif</dt>
                                    <dd class="text-white font-semibold shrink-0">{{ $e->clicks }}</dd>
                                </div>
                            @empty
                                <p class="text-slate-400">No button or link clicks yet.</p>
                            @endforelse
                        </dl>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    var points = @json($points);
                    var frame = document.getElementById('page');
                    var canvas = document.getElementById('heat');
                    var radius = {{ $device === 'mobile' ? 22 : 34 }};

                    function draw() {
                        var doc;
                        try { doc = frame.contentDocument; } catch (e) { return; }
                        if (!doc || !doc.documentElement) return;
                        var h = Math.max(doc.documentElement.scrollHeight, doc.body ? doc.body.scrollHeight : 0);
                        var w = frame.clientWidth;
                        frame.style.height = h + 'px';
                        canvas.width = w; canvas.height = h;

                        // Pass 1: stack soft grey blobs so busy spots get darker.
                        var mask = document.createElement('canvas'); mask.width = w; mask.height = h;
                        var mc = mask.getContext('2d');
                        points.forEach(function (p) {
                            var x = p.x_pct / 100 * w, y = +p.y_px;
                            var g = mc.createRadialGradient(x, y, 0, x, y, radius);
                            g.addColorStop(0, 'rgba(0,0,0,0.35)'); g.addColorStop(1, 'rgba(0,0,0,0)');
                            mc.fillStyle = g; mc.fillRect(x - radius, y - radius, radius * 2, radius * 2);
                        });

                        // Pass 2: turn how dark each pixel is into a colour, cool to warm.
                        var ramp = document.createElement('canvas'); ramp.width = 1; ramp.height = 256;
                        var rc = ramp.getContext('2d'), lg = rc.createLinearGradient(0, 0, 0, 256);
                        lg.addColorStop(0.25, '#0078D4'); lg.addColorStop(0.5, '#2ecc71'); lg.addColorStop(0.75, '#f1c40f'); lg.addColorStop(1, '#e74c3c');
                        rc.fillStyle = lg; rc.fillRect(0, 0, 1, 256);
                        var colors = rc.getImageData(0, 0, 1, 256).data;
                        var img = mc.getImageData(0, 0, w, h), d = img.data;
                        for (var i = 3; i < d.length; i += 4) {
                            var a = d[i];
                            if (!a) continue;
                            var k = Math.min(255, a * 2) * 4;
                            d[i - 3] = colors[k]; d[i - 2] = colors[k + 1]; d[i - 1] = colors[k + 2]; d[i] = Math.min(255, a * 2.2);
                        }
                        canvas.getContext('2d').putImageData(img, 0, 0);
                    }

                    frame.addEventListener('load', function () { draw(); setTimeout(draw, 800); });
                })();
            </script>
        @endif
    </div>
</x-app-layout>
