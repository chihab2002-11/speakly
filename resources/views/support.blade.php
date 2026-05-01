@php
    $currentRole = $currentRole ?? \App\Support\DashboardRedirector::roleFor(auth()->user());
    $layoutComponent = 'layouts.'.$currentRole;
@endphp

<x-dynamic-component
    :component="$layoutComponent"
    :user="$user ?? auth()->user()"
    :current-route="$currentRoute ?? 'support'"
    page-title="Support"
    title="Support"
>
    <div class="mx-auto flex max-w-6xl flex-col gap-6">
        <div class="rounded-2xl border p-6 md:p-8" style="background: white; border-color: var(--lumina-border-light, var(--lumina-border));">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide" style="color: var(--lumina-primary);">Help Center</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary);">
                        Support
                    </h1>
                    <p class="mt-3 max-w-2xl text-base leading-7" style="color: var(--lumina-text-secondary);">
                        Find quick guidance for using the platform, accessing your tools, and contacting the administration team when you need help.
                    </p>
                </div>

                <div class="flex h-14 w-14 items-center justify-center rounded-2xl" style="background: #D1FAE5; color: var(--lumina-primary);">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 11-12.728 0 9 9 0 0112.728 0zM12 9v3m0 4h.01"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="rounded-2xl border p-6 lg:col-span-2" style="background: white; border-color: var(--lumina-border-light, var(--lumina-border));">
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">How to use the platform</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border p-4" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">Start from your dashboard</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">Your dashboard shows the main actions available for your role, including courses, messages, schedules, and account tools.</p>
                    </div>
                    <div class="rounded-xl border p-4" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">Use the sidebar</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">The sidebar is the fastest way to move between pages. It updates based on your role and permissions.</p>
                    </div>
                    <div class="rounded-xl border p-4" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">Check notifications</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">Important updates from the academy appear in your notifications area.</p>
                    </div>
                    <div class="rounded-xl border p-4" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">Keep your profile current</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">Use Settings to review your contact details and password information.</p>
                    </div>
                </div>
            </section>

            <aside class="rounded-2xl border p-6" style="background: white; border-color: var(--lumina-border-light, var(--lumina-border));">
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Quick tips</h2>
                <ul class="mt-5 space-y-3 text-sm leading-6" style="color: var(--lumina-text-secondary);">
                    <li class="flex gap-3">
                        <span class="mt-2 h-2 w-2 rounded-full" style="background: var(--lumina-primary);"></span>
                        Refresh the page if something looks unchanged after saving or uploading.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2 h-2 w-2 rounded-full" style="background: var(--lumina-primary);"></span>
                        Use Messages for role-based conversations inside the platform.
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2 h-2 w-2 rounded-full" style="background: var(--lumina-primary);"></span>
                        Contact administration if a course, schedule, or account detail looks incorrect.
                    </li>
                </ul>
            </aside>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border p-6" style="background: white; border-color: var(--lumina-border-light, var(--lumina-border));">
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Common questions</h2>
                <div class="mt-5 divide-y" style="border-color: var(--lumina-border);">
                    <div class="py-4">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">How do I register?</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">Use the public Register page, choose your account type, complete the required fields, and wait for administration approval.</p>
                    </div>
                    <div class="py-4">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">How do I access courses?</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">After approval, students can view assigned courses from their dashboard and learning pages. Teachers can manage assigned groups and resources.</p>
                    </div>
                    <div class="py-4">
                        <h3 class="font-bold" style="color: var(--lumina-text-primary);">How do I contact support?</h3>
                        <p class="mt-2 text-sm leading-6" style="color: var(--lumina-text-secondary);">Contact administration directly for account, payment, schedule, or registration questions.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border p-6" style="background: white; border-color: var(--lumina-border-light, var(--lumina-border));">
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Contact / Help info</h2>
                <div class="mt-5 rounded-xl border p-5" style="border-color: var(--lumina-border); background: #F8FAFC;">
                    <p class="text-sm leading-6" style="color: var(--lumina-text-secondary);">
                        For urgent help, contact administration from the academy office. For general support, use:
                    </p>
                    <p class="mt-3 text-base font-bold" style="color: var(--lumina-text-primary);">support@speakly.com</p>
                    <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Office support can help with registration, approvals, courses, payments, schedules, and account access.</p>
                </div>
            </section>
        </div>
    </div>
</x-dynamic-component>
