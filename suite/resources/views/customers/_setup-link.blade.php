{{-- Compact login-setup action for one customer row. $customer is required.
     The full version with explanation lives on the customer profile. --}}
@if($customer->password)
    <span class="text-xs text-emerald-400">Has a login</span>
@elseif($customer->hasActiveSetupLink())
    <div class="flex flex-wrap items-center gap-x-1 gap-y-1" x-data="{ copied: false }">
        @if($customer->phone)
            <x-whatsapp-link :phone="$customer->phone" :message="$customer->setupLinkMessage()"
                class="text-xs font-medium text-slate-200 px-2 py-3 -ml-2">Send on WhatsApp</x-whatsapp-link>
        @endif
        <button type="button" data-link="{{ $customer->setupLinkUrl() }}"
                x-on:click="navigator.clipboard.writeText($el.dataset.link).then(() => { copied = true; setTimeout(() => copied = false, 2500) }).catch(() => window.prompt('Copy this link:', $el.dataset.link))"
                class="text-xs text-[#0078D4] hover:text-[#B8D4F0] px-2 py-3">
            <span x-show="!copied">Copy link</span>
            <span x-show="copied" x-cloak class="text-emerald-400">Copied</span>
        </button>
        <span class="text-xs text-slate-500 px-2">Works until {{ $customer->setup_link_expires_at->format('D j M, H:i') }}</span>
    </div>
@else
    <form method="POST" action="{{ route('customers.setup-link.store', $customer) }}">
        @csrf
        <button type="submit" class="text-xs font-medium text-[#0078D4] hover:text-[#B8D4F0] px-2 py-3 -ml-2">Create setup link</button>
    </form>
@endif
