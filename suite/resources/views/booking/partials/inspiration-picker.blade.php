{{--
    Inspiration photo picker for the customer booking portal.

    Expects to sit inside an Alpine scope that owns `inspoBusy` (the parent
    form disables its submit button while photos are being shrunk).

    @param int    $max         how many photos may still be added
    @param string $inputId     unique id for the file input (several per page on My Bookings)

    Photos are shrunk to <=1600px JPEG in the browser before upload, which
    keeps uploads small on mobile data and drops EXIF/location data at the
    source. The server re-encodes again regardless, so a browser without
    DataTransfer simply uploads the original file.
--}}
<div x-data="inspirationPicker({{ (int) $max }})" class="space-y-3">
    <input type="file" name="inspiration_photos[]" id="{{ $inputId }}" x-ref="input"
           accept="image/jpeg,image/png,image/webp" multiple class="sr-only xq-picker-input"
           @change="add($event)">

    <div x-show="items.length" x-cloak class="grid grid-cols-3 gap-2">
        <template x-for="(item, i) in items" :key="item.key">
            <div class="relative">
                <img :src="item.preview" :alt="'Inspiration photo preview ' + (i + 1)"
                     class="w-full aspect-square object-cover rounded-xl border border-slate-200">
                <button type="button" @click="remove(i)"
                        class="absolute top-1 right-1 w-8 h-8 rounded-full bg-slate-900 hover:bg-slate-700 text-white flex items-center justify-center"
                        :aria-label="'Remove photo ' + (i + 1)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <label for="{{ $inputId }}" x-show="items.length < max"
           class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border border-dashed border-slate-300 text-sm font-medium text-slate-600 hover:border-[#0078D4] hover:text-[#0078D4] cursor-pointer transition xq-focus-ring">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5V4.5A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Zm10.5-13.5h.008v.008h-.008V7.5Z"/></svg>
        <span x-text="busy ? 'Preparing photos…' : (items.length ? 'Add another photo' : 'Add photos')"></span>
    </label>
    <p class="text-xs text-slate-400">
        <span x-text="items.length"></span> of <span x-text="max"></span> &middot; JPG, PNG or WebP
    </p>
</div>

@once
<style>.xq-focus-ring:focus-within, .xq-picker-input:focus-visible ~ .xq-focus-ring{box-shadow:0 0 0 2px #0078D4}</style>
@push('scripts')
<script>
function inspirationPicker(max) {
    const EDGE = 1600;

    // Downscale to EDGE px and re-encode as JPEG; resolves to the original on any failure.
    async function shrink(file) {
        try {
            let bitmap;
            try { bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' }); }
            catch { bitmap = await createImageBitmap(file); }
            const scale  = Math.min(1, EDGE / Math.max(bitmap.width, bitmap.height));
            const canvas = document.createElement('canvas');
            canvas.width  = Math.round(bitmap.width * scale);
            canvas.height = Math.round(bitmap.height * scale);
            canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.85));
            if (!blob) return file;
            const name = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], name, { type: 'image/jpeg' });
        } catch {
            return file;
        }
    }

    return {
        max,
        items: [],
        busy: false,
        supported: typeof DataTransfer !== 'undefined',

        async add(event) {
            const picked = Array.from(event.target.files || []);
            if (!this.supported) {
                // Old browser: leave the native selection alone, just preview it.
                this.items = picked.slice(0, this.max).map((f, i) => ({ key: Date.now() + i, file: f, preview: URL.createObjectURL(f) }));
                return;
            }
            this.busy = true;
            this.inspoBusy = true;
            try {
                for (const f of picked) {
                    if (this.items.length >= this.max) break;
                    const file = await shrink(f);
                    this.items.push({ key: Date.now() + Math.random(), file, preview: URL.createObjectURL(file) });
                }
            } finally {
                this.sync();
                this.busy = false;
                this.inspoBusy = false;
            }
        },

        remove(i) {
            URL.revokeObjectURL(this.items[i].preview);
            this.items.splice(i, 1);
            if (this.supported) {
                this.sync();
            } else {
                this.$refs.input.value = '';
                this.items = [];
            }
        },

        // Rewrite the real input so the form posts exactly what's previewed.
        sync() {
            const dt = new DataTransfer();
            this.items.forEach(item => dt.items.add(item.file));
            this.$refs.input.files = dt.files;
        },
    };
}
</script>
@endpush
@endonce
