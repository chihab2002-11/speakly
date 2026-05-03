@auth
    <script>
        window.AuthUser = @json([
            'id' => auth()->id(),
        ]);
    </script>
@endauth

<div
    id="live-notification-toast"
    class="pointer-events-auto fixed right-4 top-4 z-[70] hidden w-[calc(100%-2rem)] max-w-sm rounded-xl border bg-white p-4 pr-12 shadow-xl"
    style="border-color: var(--lumina-border, #E2E8F0);"
    role="status"
    aria-live="polite"
>
    <button
        type="button"
        data-live-toast-close
        class="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-full transition hover:bg-gray-100"
        style="color: var(--lumina-text-muted, #64748B);"
        aria-label="Close notification"
        title="Close notification"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <p data-live-toast-title class="text-sm font-bold" style="color: var(--lumina-text-primary, #0F172A);"></p>
    <p data-live-toast-message class="mt-1 text-sm" style="color: var(--lumina-text-secondary, #475569);"></p>
</div>
