@props(['section'])

<div class="pointer-events-none absolute right-2 top-2 z-20 flex gap-1 rounded-lg bg-slate-900/85 p-1 opacity-0 transition-opacity group-hover/xq-section:pointer-events-auto group-hover/xq-section:opacity-100">
    <button type="button" title="Edit"
            onclick="parent.postMessage({type:'xq-section-action',action:'edit',sectionId:{{ $section->id }}}, window.location.origin)"
            class="rounded px-2 py-1 text-xs text-white hover:bg-white/10">
        <i class="fa fa-pencil"></i>
    </button>
    <button type="button" title="Duplicate"
            onclick="parent.postMessage({type:'xq-section-action',action:'duplicate',sectionId:{{ $section->id }}}, window.location.origin)"
            class="rounded px-2 py-1 text-xs text-white hover:bg-white/10">
        <i class="fa fa-clone"></i>
    </button>
    <button type="button" title="{{ $section->is_visible ? 'Hide' : 'Show' }}"
            onclick="parent.postMessage({type:'xq-section-action',action:'toggle-visibility',sectionId:{{ $section->id }}}, window.location.origin)"
            class="rounded px-2 py-1 text-xs text-white hover:bg-white/10">
        <i class="fa {{ $section->is_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
    </button>
    <button type="button" title="Delete"
            onclick="parent.postMessage({type:'xq-section-action',action:'delete',sectionId:{{ $section->id }}}, window.location.origin)"
            class="rounded px-2 py-1 text-xs text-white hover:bg-red-500/30">
        <i class="fa fa-trash"></i>
    </button>
</div>
