@once
@push('css')
<style>
.upah-workspace { --upah-line: #d7e2dc; --upah-ink: #17382d; max-width: 1480px; }
.upah-heading { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1.25rem; margin-bottom: 1.5rem; }
.upah-heading h1, .upah-panel h1 { color: var(--upah-ink); font-size: clamp(1.55rem, 3vw, 2.1rem); font-weight: 750; letter-spacing: -.025em; }
.upah-heading nav a, .upah-back { color: #285844; font-weight: 600; text-decoration: none; }
.upah-heading nav a:hover, .upah-back:hover { text-decoration: underline; }
.upah-primary { min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }
.upah-panel { background: #fff; border: 1px solid var(--upah-line); border-radius: .9rem; box-shadow: 0 .2rem .9rem rgba(23, 56, 45, .06); }
.upah-panel-heading { padding: 1rem 1.25rem; border-bottom: 1px solid var(--upah-line); }
.upah-panel .table { --bs-table-border-color: #c9d7cf; }
.upah-panel .table th { white-space: nowrap; }
.upah-panel .table td, .upah-panel .table th { padding: .65rem .7rem; }
.upah-narrow { max-width: 720px; }
.upah-icon { display: inline-grid; place-items: center; width: 48px; height: 48px; background: #e7f2ec; color: #245b42; border-radius: .75rem; font-size: 1.25rem; }
.upah-modal .modal-header { background: #eef5f1; padding-inline: 1.5rem; }
.upah-modal .modal-footer { background: #f8faf9; }
.upah-form-section { border-top: 1px solid #d7e2dc; padding-top: 1rem; margin-bottom: .85rem; }
.upah-activate { border: 0; border-radius: 1rem; overflow: hidden; }
.upah-activate .modal-header, .upah-activate .modal-footer { padding: 1rem 1.5rem; border-color: #d7e2dc; }
.upah-activate .modal-title { color: #17382d; font-size: clamp(1.2rem, 3vw, 1.55rem); font-weight: 750; letter-spacing: -.02em; }
.upah-activate .modal-body { padding: 1.25rem 1.5rem; background: #f6f9f7; }
.upah-activate-section { border: 1px solid #d7e2dc; border-radius: .75rem; padding: 1rem; background: #fff; }
.upah-activate-section + .upah-activate-section { margin-top: .9rem; }
.upah-activate-section h3 { color: #17382d; font-size: 1rem; font-weight: 700; margin-bottom: .9rem; }
.upah-activate-section .form-label { color: #30483e; font-size: .875rem; font-weight: 600; }
.upah-activate-section .form-control, .upah-activate-section .select2-selection { min-height: 44px; border-color: #aebfb5; }
.upah-activate-section .input-group-text { background: #eef5f1; color: #365447; }
.upah-activate-section .select2-container--bootstrap-5 .select2-selection--single { align-content: center; }
@media (max-width: 575.98px) {
    .upah-heading > .upah-primary { width: 100%; }
    .upah-action-row > .btn { flex: 1 1 100%; }
    .upah-activate { border-radius: 0; }
    .upah-activate .modal-header, .upah-activate .modal-body, .upah-activate .modal-footer { padding-inline: 1rem; }
    .upah-activate .modal-footer > .btn { flex: 1 1 auto; }
}
</style>
@endpush
@endonce
