@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'cards';
    $items = collect($content['items'] ?? []);
    $gridColsClass = match (min(4, max(2, $items->count()))) {
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        default => 'lg:grid-cols-4',
    };
@endphp

<section class="bg-[var(--site-bg)] py-20">
    <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
        @if($content['heading'] ?? null)
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
        @endif
        @if($content['subtext'] ?? null)
            <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
        @endif

        @if($items->isNotEmpty())
            @if($variant === 'table')
                <div class="mt-12 overflow-x-auto">
                    <table class="mx-auto w-full max-w-4xl text-left">
                        <thead>
                            <tr class="border-b border-[var(--site-border)]">
                                <th class="py-3 text-sm font-semibold text-[var(--site-text)]">Plan</th>
                                <th class="py-3 text-sm font-semibold text-[var(--site-text)]">Price</th>
                                <th class="py-3 text-sm font-semibold text-[var(--site-text)]">Includes</th>
                                <th class="py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr class="border-b border-[var(--site-border)]">
                                    <td class="py-4 font-semibold text-[var(--site-text)]">{{ $item['name'] ?? '' }}</td>
                                    <td class="py-4 text-[var(--site-text)]">{{ $item['price'] ?? '' }}<span class="text-xs text-[var(--site-text-faint)]"> {{ $item['period'] ?? '' }}</span></td>
                                    <td class="py-4 text-sm text-[var(--site-text-muted)]">{{ collect($item['features'] ?? [])->pluck('text')->implode(', ') }}</td>
                                    <td class="py-4 text-right">
                                        <a href="{{ $item['cta_link'] ?? '#contact' }}" class="rounded-lg bg-[var(--tenant-primary)] px-4 py-2 text-xs font-semibold text-white hover:brightness-90">{{ $item['cta_text'] ?? 'Get Started' }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="mt-12 grid gap-6 sm:grid-cols-2 {{ $gridColsClass }}">
                    @foreach($items as $item)
                        @php $highlighted = filter_var($item['highlighted'] ?? false, FILTER_VALIDATE_BOOLEAN); @endphp
                        <div class="flex flex-col rounded-xl border p-6 text-left" style="{{ $highlighted ? 'border-color: var(--tenant-accent); background: color-mix(in srgb, var(--tenant-accent) 6%, transparent);' : '' }}" @class(['border-[var(--site-border)] bg-[var(--site-surface)]' => !$highlighted])>
                            <div class="text-center">
                                <span class="text-3xl font-extrabold text-[var(--site-text)]">{{ $item['price'] ?? '' }}</span>
                                @if($item['period'] ?? null)
                                    <span class="block text-xs uppercase tracking-wide text-[var(--site-text-faint)]">{{ $item['period'] }}</span>
                                @endif
                                <span class="mt-2 block font-display text-lg font-semibold" style="{{ $highlighted ? 'color: var(--tenant-accent);' : '' }}" @class(['text-[var(--site-text)]' => !$highlighted])>{{ $item['name'] ?? '' }}</span>
                            </div>
                            <ul class="mt-6 flex-1 space-y-2 text-sm text-[var(--site-text-muted)]">
                                @foreach(($item['features'] ?? []) as $feature)
                                    <li class="border-t border-[var(--site-border)] pt-2 first:border-0 first:pt-0">{{ $feature['text'] ?? '' }}</li>
                                @endforeach
                            </ul>
                            <a
                                href="{{ $item['cta_link'] ?? '#contact' }}"
                                class="mt-6 block rounded-lg px-4 py-2.5 text-center text-sm font-semibold transition {{ $highlighted ? 'text-[var(--site-text)]' : 'bg-[var(--tenant-primary)] text-white hover:brightness-90' }}"
                                style="{{ $highlighted ? 'background: var(--tenant-accent);' : '' }}"
                            >{{ $item['cta_text'] ?? 'Get Started' }}</a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</section>
