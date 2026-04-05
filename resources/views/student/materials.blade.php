<x-layouts.student :title="__('Learning Materials')" :currentRoute="'materials'">
    {{-- Header Section --}}
    <div class="mb-10 flex flex-col gap-2">
        <h1 class="font-inter text-5xl font-black" style="color: #181D19; letter-spacing: -1.2px;">
            Learning Materials
        </h1>
        <p class="max-w-2xl text-lg" style="color: #3F4941; line-height: 28px;">
            Access your academic curriculum, research papers, and homework assignments in one centralized cognitive hub.
        </p>
    </div>

    {{-- Language Selector Dropdown --}}
    <div class="mb-8 flex items-center gap-4">
        <span class="text-sm font-semibold" style="color: #3F4941;">Filter by Language:</span>
        <div class="relative">
            <select 
                class="appearance-none rounded-lg border bg-white px-4 py-2 pr-10 text-sm font-medium transition-colors focus:outline-none focus:ring-2"
                style="border-color: rgba(190, 201, 191, 0.3); color: #181D19; min-width: 180px;"
                onchange="filterByLanguage(this.value)"
            >
                <option value="all">All Languages</option>
                <option value="english" selected>English</option>
                <option value="french">French</option>
                <option value="spanish">Spanish</option>
                <option value="german">German</option>
                <option value="arabic">Arabic</option>
            </select>
            {{-- Dropdown Arrow --}}
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
                <svg class="h-4 w-4" fill="currentColor" style="color: #3F4941;" viewBox="0 0 24 24">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="flex flex-col gap-10">
        {{-- Homework Assignments Section --}}
        <div class="flex flex-col gap-6">
            {{-- Section Header --}}
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" style="color: #181D19;">
                    Homework assignments
                </h2>
                <span 
                    class="rounded-full px-3 py-1 text-xs font-bold"
                    style="background-color: #C1E6CC; color: #476853;"
                >
                    {{ count($assignments ?? []) ?: 3 }}
                </span>
            </div>

            {{-- Assignment Cards --}}
            <div class="flex flex-col gap-4">
                @php
                    $assignments = $assignments ?? [
                        [
                            'title' => 'Advanced Syntax Analysis',
                            'due' => 'Due: tomorrow at 23:59',
                            'course' => 'Linguistics 301',
                            'icon' => 'document',
                        ],
                        [
                            'title' => 'Contextual Semantics Case Study',
                            'due' => 'Due: Thursday, Oct 24',
                            'course' => 'Philology',
                            'icon' => 'translate',
                        ],
                        [
                            'title' => 'Etymology of Modern Dialects',
                            'due' => 'Due: Monday, Oct 28',
                            'course' => 'Advanced English',
                            'icon' => 'book',
                        ],
                    ];
                @endphp

                @foreach($assignments as $assignment)
                    <div 
                        class="flex items-center justify-between rounded-xl border p-6"
                        style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1);"
                    >
                        {{-- Left: Icon + Info --}}
                        <div class="flex items-center gap-4">
                            {{-- Icon --}}
                            <div 
                                class="flex h-12 w-12 items-center justify-center rounded-lg"
                                style="background-color: #F0F5EE;"
                            >
                                @if($assignment['icon'] === 'document')
                                    <svg class="h-5 w-5" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                    </svg>
                                @elseif($assignment['icon'] === 'translate')
                                    <svg class="h-6 w-6" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                                        <path d="m12.87 15.07-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                                        <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex flex-col">
                                <span class="text-lg font-bold" style="color: #181D19;">
                                    {{ $assignment['title'] }}
                                </span>
                                <span class="text-sm" style="color: #3F4941;">
                                    {{ $assignment['due'] }} &bull; {{ $assignment['course'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: Print Button --}}
                        <button 
                            class="flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold transition-colors hover:bg-gray-50"
                            style="border-color: rgba(190, 201, 191, 0.3); color: #3F4941;"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/>
                            </svg>
                            Print
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Library Section --}}
        <div 
            class="flex flex-col gap-6 rounded-2xl border p-8"
            style="background-color: #F0F5EE; border-color: rgba(190, 201, 191, 0.2);"
        >
            {{-- Section Header --}}
            <div class="flex items-center gap-3">
                <svg class="h-7 w-7" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                    <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9h-4v4h-2v-4H9V9h4V5h2v4h4v2z"/>
                </svg>
                <h2 class="text-2xl font-bold" style="color: #181D19;">
                    Library
                </h2>
            </div>

            {{-- Library Items --}}
            <div class="flex flex-col gap-6">
                @php
                    $libraryItems = $libraryItems ?? [
                        [
                            'title' => 'Mastering Subjunctive PDF',
                            'size' => '14.2 MB',
                            'category' => 'GRAMMAR ESSENTIALS',
                            'type' => 'pdf',
                        ],
                        [
                            'title' => 'Idiomatic Expressions Guide',
                            'size' => '4.8 MB',
                            'category' => 'VOCABULARY',
                            'type' => 'doc',
                        ],
                    ];
                @endphp

                @foreach($libraryItems as $item)
                    <div 
                        class="flex items-center justify-between rounded-xl p-4"
                        style="background-color: #FFFFFF; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
                    >
                        {{-- Left: Icon + Info --}}
                        <div class="flex items-center gap-4">
                            {{-- File Type Icon --}}
                            <div 
                                class="flex h-12 w-10 items-center justify-center rounded"
                                style="background-color: {{ $item['type'] === 'pdf' ? 'rgba(255, 218, 214, 0.2)' : 'rgba(35, 132, 87, 0.2)' }}; border: 1px solid {{ $item['type'] === 'pdf' ? 'rgba(186, 26, 26, 0.1)' : 'rgba(0, 106, 65, 0.1)' }};"
                            >
                                @if($item['type'] === 'pdf')
                                    <svg class="h-5 w-5" fill="currentColor" style="color: #BA1A1A;" viewBox="0 0 24 24">
                                        <path d="M20 2H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-8.5 7.5c0 .83-.67 1.5-1.5 1.5H9v2H7.5V7H10c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V7H15c.83 0 1.5.67 1.5 1.5v3zm4-3H19v1h1.5V11H19v2h-1.5V7h3v1.5zM9 9.5h1v-1H9v1zM4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm10 5.5h1v-3h-1v3z"/>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="flex flex-col">
                                <span class="text-sm font-bold leading-tight" style="color: #181D19;">
                                    {{ $item['title'] }}
                                </span>
                                <span class="mt-1 text-xs uppercase tracking-tight" style="color: #3F4941; letter-spacing: -0.55px;">
                                    {{ $item['size'] }} &bull; {{ $item['category'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: Download Button --}}
                        <button 
                            class="flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-gray-100"
                        >
                            <svg class="h-4 w-4" fill="currentColor" style="color: #006A41;" viewBox="0 0 24 24">
                                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                            </svg>
                        </button>
                    </div>
                @endforeach

                {{-- View Full Archive Button --}}
                <button 
                    class="flex w-full items-center justify-center rounded-xl border-2 border-dashed py-4 text-sm font-bold transition-colors hover:bg-white/50"
                    style="border-color: rgba(0, 106, 65, 0.3); color: #006A41;"
                >
                    View Full Archive
                </button>
            </div>
        </div>
    </div>

    {{-- JavaScript for language filter (placeholder functionality) --}}
    <script>
        function filterByLanguage(language) {
            // This is a placeholder - backend team will implement actual filtering
            console.log('Filtering materials by language:', language);
            // In production, this would make an AJAX request or update the page
        }
    </script>
</x-layouts.student>
