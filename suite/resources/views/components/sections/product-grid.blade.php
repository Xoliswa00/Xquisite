@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'grid';
    $hasEcommerce = $tenant->hasModule('ecommerce');
    $products = collect();

    if ($hasEcommerce) {
        $limit = (int) ($content['limit'] ?? 8) ?: 8;
        $query = \App\Modules\POS\Models\Product::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true);

        $products = match ($content['source'] ?? 'featured') {
            'manual' => (clone $query)->whereIn('id', $content['product_ids'] ?? [])->limit($limit)->get(),
            'latest' => (clone $query)->latest()->limit($limit)->get(),
            default => (clone $query)->limit($limit)->get(),
        };
    }
@endphp

@if($hasEcommerce)
    <section class="bg-[var(--site-bg)] py-20">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif
            @if($content['subtext'] ?? null)
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
            @endif

            @if($products->isNotEmpty())
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($products as $product)
                        <a href="{{ route('shop.product', [$tenant->slug, $product->id]) }}" class="group overflow-hidden rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] text-left transition hover:border-[var(--tenant-primary)]/50">
                            <div class="aspect-square overflow-hidden bg-[var(--site-bg)]">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="text-sm font-semibold text-[var(--site-text)]">{{ $product->name }}</h3>
                                <p class="mt-1 text-sm font-medium text-[var(--tenant-primary)]">R{{ number_format($product->price, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('shop.index', $tenant->slug) }}" class="mt-10 inline-block rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90">
                Visit the Shop
            </a>
        </div>
    </section>
@elseif($editing)
    <section class="border-2 border-dashed border-[var(--site-border-2)] bg-[var(--site-surface)] py-16">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6">
            <p class="text-sm text-[var(--site-text-muted)]">This Product Grid section is hidden from visitors until the Store is active.</p>
            <a href="{{ route('settings.modules.index') }}" class="mt-3 inline-block text-sm font-medium text-[var(--tenant-primary)] hover:underline">Activate the Store →</a>
        </div>
    </section>
@endif
