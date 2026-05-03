@auth
    <script>
        window.AuthUser = @json([
            'id' => auth()->id(),
        ]);
    </script>
@endauth

<div
    id="live-notification-toast"
    class="pointer-events-none fixed right-4 top-4 z-[70] hidden w-[calc(100%-2rem)] max-w-sm rounded-xl border bg-white p-4 shadow-xl"
    style="border-color: var(--lumina-border, #E2E8F0);"
    role="status"
    aria-live="polite"
>
    <p data-live-toast-title class="text-sm font-bold" style="color: var(--lumina-text-primary, #0F172A);"></p>
    <p data-live-toast-message class="mt-1 text-sm" style="color: var(--lumina-text-secondary, #475569);"></p>
</div>
