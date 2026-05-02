<x-layouts.teacher :title="__('Teaching Resources')" :currentRoute="'resources'">
    {{--
    ================================================================================
    BACKEND SUMMARY: Teaching Resources Page
    ================================================================================
    
    API Endpoints Required:
    -----------------------
    1. GET /api/teacher/resources
       - Returns: List of all resources uploaded by teacher
       - Query params: ?category_id, ?class_id, ?search, ?sort_by, ?page
       - Response: { data: Resource[], meta: { total, per_page, current_page } }
    
    2. GET /api/teacher/resources/categories
       - Returns: List of resource categories (Homeworks, Course Materials)
       - Response: { data: Category[] }
    
    3. GET /api/teacher/classes
       - Returns: List of classes assigned to teacher
       - Response: { classes: [{ id, name, students_count }] }
    
    4. POST /api/teacher/resources
       - Creates new resource
       - Request: multipart/form-data { file, name, class_id, category_id, deadline, description }
       - Response: { data: Resource }
    
    5. DELETE /api/teacher/resources/{id}
       - Deletes a resource
       - Response: { success: true }
    
    6. GET /api/teacher/resources/{id}/download
       - Returns file download
    
    Expected Response Formats:
    --------------------------
    Resource: {
        id: number,
        name: string,
        type: string (PDF, DOC, DOCX, ZIP),
        size: string,
        uploaded_at: datetime,
        downloads: number,
        class_id: number,
        category_id: number (1=Homeworks, 2=Course Materials),
        deadline: string|null,
        description: string|null
    }
    
    Category: {
        id: number,
        name: string (Homeworks | Course Materials),
        count: number,
        icon: string (homework | course_materials)
    }
    ================================================================================
    --}}

    @php
        $hasClasses = ! empty($classes);
        $search = $activeFilters['search'] ?? '';
        $selectedCategory = $activeFilters['category_id'] ?? '';
        $selectedFileType = $activeFilters['file_type'] ?? '';
        $selectedSort = $activeFilters['sort_by'] ?? 'recent';
        $selectedClassId = $activeFilters['class_id'] ?? '';
        $homeworkCategory = \App\Models\TeacherResource::CATEGORY_HOMEWORK;
        $courseMaterialsCategory = \App\Models\TeacherResource::CATEGORY_COURSE_MATERIALS;
        $resourceCategoryStyles = [
            $homeworkCategory => [
                'label' => 'Homework',
                'background' => '#FFEDD5',
                'border' => '#FDBA74',
                'text' => '#C2410C',
                'icon' => '#F97316',
            ],
            $courseMaterialsCategory => [
                'label' => 'Course Material',
                'background' => '#DBEAFE',
                'border' => '#BFDBFE',
                'text' => '#1D4ED8',
                'icon' => '#2563EB',
            ],
        ];

        $baseFilterQuery = array_filter([
            'search' => $search,
            'class_id' => $selectedClassId,
            'file_type' => $selectedFileType,
            'sort_by' => $selectedSort !== 'recent' ? $selectedSort : '',
        ], fn (mixed $value): bool => $value !== '' && $value !== null);
    @endphp

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Teaching Resources
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Upload, manage, and share learning materials with your students.
            </p>
        </div>

        {{-- Upload Button --}}
        <button
            @if($hasClasses)
                onclick="document.getElementById('uploadModal').classList.remove('hidden')"
            @endif
            class="inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold text-white transition-all hover:opacity-90 hover:scale-[1.02] active:scale-[0.98] {{ $hasClasses ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
            style="background-color: var(--lumina-primary);"
            @disabled(! $hasClasses)
            title="{{ $hasClasses ? 'Upload a new resource' : 'No assigned classes available for upload' }}"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Resource
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border p-4" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border p-4" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless($hasClasses)
        <div class="mb-4 rounded-xl border p-4" style="background-color: #FFFBEB; border-color: #FDE68A; color: #92400E;">
            <p class="text-sm font-semibold">You cannot upload resources yet because no classes are assigned to your account.</p>
        </div>
    @endunless

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        {{-- Total Resources --}}
        <div 
            class="rounded-2xl border p-4 transition-all hover:shadow-md"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: var(--lumina-accent-green-bg);">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold" style="color: var(--lumina-text-primary);">{{ $resourceStats['total'] ?? 0 }}</p>
            <p class="text-xs" style="color: var(--lumina-text-muted);">Total Resources</p>
        </div>

        {{-- Downloads This Month --}}
        <div 
            class="rounded-2xl border p-4 transition-all hover:shadow-md"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #DBEAFE;">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #3B82F6;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
            <p class="text-2xl font-bold" style="color: var(--lumina-text-primary);">{{ $resourceStats['downloads'] ?? 0 }}</p>
            <p class="text-xs" style="color: var(--lumina-text-muted);">Downloads This Month</p>
        </div>

        {{-- Storage Used --}}
        <div 
            class="rounded-2xl border p-4 transition-all hover:shadow-md"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #FEF3C7;">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #F59E0B;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
            <p class="text-2xl font-bold" style="color: var(--lumina-text-primary);">{{ $resourceStats['storage'] ?? '0 B' }}</p>
            <p class="text-xs" style="color: var(--lumina-text-muted);">Storage Used</p>
        </div>

        {{-- Categories --}}
        <div 
            class="rounded-2xl border p-4 transition-all hover:shadow-md"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #F3E8FF;">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #9333EA;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold" style="color: var(--lumina-text-primary);">{{ count($categories) }}</p>
            <p class="text-xs" style="color: var(--lumina-text-muted);">Categories</p>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Categories Sidebar --}}
        <div class="lg:col-span-1">
            <div 
                class="overflow-hidden rounded-2xl border"
                style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
            >
                <div class="border-b p-4" style="border-color: var(--lumina-border);">
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Categories
                    </h3>
                </div>
                <div class="divide-y" style="border-color: var(--lumina-border);">
                    @foreach($categories as $category)
                        @php
                            $isSelectedCategory = $selectedCategory === $category['id'];
                            $categoryFilterQuery = $isSelectedCategory
                                ? $baseFilterQuery
                                : array_merge($baseFilterQuery, ['category_id' => $category['id']]);
                            $categoryStyle = $resourceCategoryStyles[$category['id']] ?? [
                                'background' => 'var(--lumina-accent-green-bg)',
                                'border' => 'var(--lumina-border-light)',
                                'text' => 'var(--lumina-primary)',
                                'icon' => 'var(--lumina-primary)',
                            ];
                        @endphp
                        <a 
                            href="{{ route('teacher.resources', $categoryFilterQuery) }}"
                            class="flex items-center gap-3 p-4 transition-all hover:bg-gray-50 {{ $isSelectedCategory ? 'bg-gray-50' : '' }}"
                            style="{{ $isSelectedCategory ? 'background-color: '.$categoryStyle['background'].';' : '' }}"
                        >
                            {{-- Category Icon --}}
                            <div 
                                class="flex h-10 w-10 items-center justify-center rounded-xl border"
                                style="background-color: {{ $categoryStyle['background'] }}; border-color: {{ $categoryStyle['border'] }};"
                            >
                                @if($category['icon'] === 'homework')
                                    {{-- Homework icon (clipboard/checklist) --}}
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $categoryStyle['icon'] }};">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                @elseif($category['icon'] === 'course_materials')
                                    {{-- Course Materials icon (book/document) --}}
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $categoryStyle['icon'] }};">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                @else
                                    {{-- Default folder icon --}}
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $categoryStyle['icon'] }};">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Category Info --}}
                            <div class="flex-1">
                                <h4 class="text-sm font-semibold" style="color: {{ $categoryStyle['text'] }};">
                                    {{ $category['name'] }}
                                </h4>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">
                                    {{ $category['count'] }} files
                                </p>
                            </div>

                            {{-- Arrow --}}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Resources List --}}
        <div class="lg:col-span-2">
            {{-- Search and Filter Bar --}}
            <form
                method="GET"
                action="{{ route('teacher.resources') }}"
                class="mb-4 flex flex-col gap-3 rounded-2xl border p-4"
                style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
            >
                @if($selectedCategory !== '')
                    <input type="hidden" name="category_id" value="{{ $selectedCategory }}">
                @endif

                {{-- Search Input --}}
                <div 
                    class="flex items-center gap-2 rounded-xl px-4 py-2"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search resources..." 
                        class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400"
                    >
                </div>

                {{-- Filter/Sort --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select
                            name="class_id"
                            class="rounded-xl border px-4 py-2 text-sm outline-none cursor-pointer"
                            style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                        >
                            <option value="">All Classes</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class['id'] }}" @selected((string) $class['id'] === $selectedClassId)>
                                    {{ $class['name'] }}
                                </option>
                            @endforeach
                        </select>

                    <select 
                        name="file_type"
                        class="rounded-xl border px-4 py-2 text-sm outline-none cursor-pointer"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                        <option value="" @selected($selectedFileType === '')>All Types</option>
                        <option value="pdf" @selected($selectedFileType === 'pdf')>PDF</option>
                        <option value="doc" @selected($selectedFileType === 'doc')>DOC</option>
                        <option value="docx" @selected($selectedFileType === 'docx')>DOCX</option>
                        <option value="zip" @selected($selectedFileType === 'zip')>ZIP</option>
                    </select>
                    <select 
                        name="sort_by"
                        class="rounded-xl border px-4 py-2 text-sm outline-none cursor-pointer"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                        <option value="recent" @selected($selectedSort === 'recent')>Most Recent</option>
                        <option value="downloads" @selected($selectedSort === 'downloads')>Most Downloads</option>
                        <option value="name" @selected($selectedSort === 'name')>Name A-Z</option>
                        <option value="size" @selected($selectedSort === 'size')>Size</option>
                    </select>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:opacity-90 cursor-pointer"
                            style="background-color: var(--lumina-primary);"
                        >
                            Apply
                        </button>
                        <a
                            href="{{ route('teacher.resources') }}"
                            class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all hover:bg-gray-50"
                            style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                        >
                            Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Recent Resources Section --}}
            <div 
                class="overflow-hidden rounded-2xl border"
                style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
            >
                <div class="border-b p-4" style="border-color: var(--lumina-border);">
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Recent Uploads
                    </h3>
                </div>

                {{-- Resource Items --}}
                <div class="divide-y" style="border-color: var(--lumina-border);">
                    @forelse($recentResources as $resource)
                        @php
                            $resourceCategoryStyle = $resourceCategoryStyles[$resource['category_id'] ?? ''] ?? [
                                'label' => 'Resource',
                                'background' => 'var(--lumina-bg-card)',
                                'border' => 'var(--lumina-border-light)',
                                'text' => 'var(--lumina-text-muted)',
                                'icon' => 'var(--lumina-text-muted)',
                            ];
                        @endphp
                        <div class="flex items-center gap-4 p-4 transition-all hover:bg-gray-50">
                            {{-- File Type Icon --}}
                            <div 
                                class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl border"
                                style="background-color: {{ $resourceCategoryStyle['background'] }}; border-color: {{ $resourceCategoryStyle['border'] }};"
                            >
                                {{-- PDF Document Icon --}}
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $resourceCategoryStyle['icon'] }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            {{-- Resource Info --}}
                            <div class="min-w-0 flex-1">
                                <h4 class="truncate text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                    {{ $resource['name'] }}
                                </h4>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs" style="color: var(--lumina-text-muted);">
                                    <span class="rounded-full px-2 py-0.5" style="background-color: var(--lumina-bg-card);">
                                        {{ $resource['type'] }}
                                    </span>
                                    <span class="rounded-full border px-2 py-0.5 font-semibold" style="background-color: {{ $resourceCategoryStyle['background'] }}; border-color: {{ $resourceCategoryStyle['border'] }}; color: {{ $resourceCategoryStyle['text'] }};">
                                        {{ $resourceCategoryStyle['label'] }}
                                    </span>
                                    <span>{{ $resource['size'] }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $resource['uploaded_at']->diffForHumans() }}</span>
                                    <span>&bull;</span>
                                    <span>{{ $resource['downloads'] }} downloads</span>
                                    @if(($resource['category_id'] ?? '') === $homeworkCategory && ! empty($resource['deadline']))
                                        <span>&bull;</span>
                                        <span>Deadline: {{ $resource['deadline'] }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2">
                                {{-- Download Button --}}
                                <a
                                    href="{{ route('teacher.resources.download', $resource['id']) }}"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition-all hover:bg-gray-100 cursor-pointer"
                                    title="Download"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>

                                {{-- Edit Button --}}
                                <button
                                    type="button"
                                    data-resource-id="{{ $resource['id'] }}"
                                    data-resource-name="{{ $resource['name'] }}"
                                    data-resource-description="{{ $resource['description'] ?? '' }}"
                                    data-resource-category-id="{{ $resource['category_id'] ?? '' }}"
                                    data-resource-class-id="{{ $resource['class_id'] ?? '' }}"
                                    data-resource-deadline="{{ $resource['deadline'] ?? '' }}"
                                    onclick="openEditResourceModal(this)"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg transition-all hover:bg-gray-100 cursor-pointer"
                                    title="Edit"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('teacher.resources.destroy', $resource['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        class="flex h-9 w-9 items-center justify-center rounded-lg transition-all hover:bg-red-50 cursor-pointer"
                                        title="Delete"
                                        onclick="return confirm('Delete this resource?')"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #EF4444;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div 
                                class="mb-4 flex h-20 w-20 items-center justify-center rounded-full"
                                style="background-color: var(--lumina-bg-card);"
                            >
                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                No Resources Yet
                            </h3>
                            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">
                                Upload your first resource to get started.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Quick Upload Drop Zone --}}
            <div 
                class="mt-4 rounded-2xl border-2 border-dashed p-8 text-center transition-all hover:border-solid"
                style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
            >
                <div class="flex flex-col items-center">
                    <div 
                        class="mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                        style="background-color: var(--lumina-accent-green-bg);"
                    >
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Drag and drop files here, or 
                        <button 
                            onclick="document.getElementById('uploadModal').classList.remove('hidden')"
                            class="font-semibold underline cursor-pointer"
                            style="color: var(--lumina-primary);"
                        >
                            browse
                        </button>
                    </p>
                    <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">
                        PDF, DOC, DOCX, ZIP up to {{ $maxUploadSizeLabel ?? '50 MB' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Upload Modal --}}
    <div 
        id="uploadModal" 
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        data-open-on-load="{{ old('resource_form') === 'upload' ? '1' : '0' }}"
        onclick="if(event.target === this) this.classList.add('hidden')"
    >
        <div 
            class="w-full max-w-md rounded-3xl p-6"
            style="background-color: #FFFFFF;"
            onclick="event.stopPropagation()"
        >
            {{-- Modal Header --}}
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">
                    Upload Resource
                </h3>
                <button 
                    onclick="document.getElementById('uploadModal').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 cursor-pointer"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Upload Form --}}
            <form action="{{ route('teacher.resources.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="resource_form" value="upload">
                
                {{-- File Input --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        File
                    </label>
                    <div 
                        class="rounded-xl border-2 border-dashed p-6 text-center"
                        style="border-color: var(--lumina-border);"
                    >
                        <input type="file" name="file" class="hidden" id="fileInput" accept=".pdf,.doc,.docx,.zip" required>
                        <label for="fileInput" class="cursor-pointer">
                            <svg class="mx-auto h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p id="fileInputLabel" class="truncate text-sm" style="color: var(--lumina-text-muted);">
                                Click to select file
                            </p>
                            <p id="fileInputHint" class="text-xs mt-1" style="color: var(--lumina-text-muted);">
                                PDF, DOC, DOCX, ZIP up to {{ $maxUploadSizeLabel ?? '50 MB' }}
                            </p>
                        </label>
                    </div>
                </div>

                {{-- Select Class --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Select Class
                    </label>
                    <div class="relative">
                        <select 
                            name="class_id"
                            required
                            class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none cursor-pointer"
                            style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                        >
                            <option value="">Select a class</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class['id'] }}" @selected((string) old('class_id', $selectedClassId) === (string) $class['id'])>
                                    {{ $class['name'] }} ({{ $class['students_count'] }} students)
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Resource Name --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Resource Name
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        value="{{ old('name') }}"
                        required
                        placeholder="Enter resource name"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Category
                    </label>
                    <select 
                        id="uploadResourceCategory"
                        name="category_id"
                        required
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none cursor-pointer"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}" @selected(old('category_id') === $category['id'])>
                                {{ $category['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div
                        id="uploadCategoryColorPreview"
                        class="mt-2 hidden rounded-xl border px-3 py-2 text-xs font-semibold"
                        aria-live="polite"
                    ></div>
                </div>

                {{-- Homework Deadline --}}
                <div id="uploadDeadlineGroup" class="mb-4 hidden">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Homework Deadline
                    </label>
                    <input
                        id="uploadDeadlineInput"
                        type="date"
                        name="deadline"
                        value="{{ old('deadline') }}"
                        min="{{ now()->toDateString() }}"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Description (optional)
                    </label>
                    <textarea 
                        name="description" 
                        rows="3"
                        placeholder="Brief description of the resource"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 resize-none"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >{{ old('description') }}</textarea>
                </div>

                {{-- Submit Button --}}
                <div class="flex gap-3">
                    <button 
                        type="button"
                        onclick="document.getElementById('uploadModal').classList.add('hidden')"
                        class="flex-1 rounded-xl border px-4 py-3 text-sm font-semibold transition-all hover:bg-gray-50 cursor-pointer"
                        style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        @disabled(! $hasClasses)
                        class="flex-1 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all hover:opacity-90 cursor-pointer"
                        style="background-color: var(--lumina-primary);"
                    >
                        Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div
        id="editModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        data-open-on-load="{{ old('resource_form') === 'update' ? '1' : '0' }}"
        data-old-resource-id="{{ old('resource_id') }}"
        data-old-resource-name="{{ old('name') }}"
        data-old-resource-description="{{ old('description', '') }}"
        data-old-resource-category-id="{{ old('category_id') }}"
        data-old-resource-class-id="{{ old('class_id') }}"
        data-old-resource-deadline="{{ old('deadline') }}"
        data-update-url-template="{{ route('teacher.resources.update', ['resource' => '__RESOURCE__']) }}"
        onclick="if(event.target === this) closeEditResourceModal()"
    >
        <div
            class="w-full max-w-md rounded-3xl p-6"
            style="background-color: #FFFFFF;"
            onclick="event.stopPropagation()"
        >
            {{-- Modal Header --}}
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">
                    Edit Resource
                </h3>
                <button
                    type="button"
                    onclick="closeEditResourceModal()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 cursor-pointer"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editResourceForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <input type="hidden" name="resource_form" value="update">
                <input type="hidden" name="resource_id" id="editResourceId" value="">

                {{-- Select Class --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Select Class
                    </label>
                    <div class="relative">
                        <select
                            id="editResourceClass"
                            name="class_id"
                            required
                            class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none cursor-pointer"
                            style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                        >
                            <option value="">Select a class</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class['id'] }}">
                                    {{ $class['name'] }} ({{ $class['students_count'] }} students)
                                </option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Resource Name --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Resource Name
                    </label>
                    <input
                        id="editResourceName"
                        type="text"
                        name="name"
                        required
                        placeholder="Enter resource name"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Category
                    </label>
                    <select
                        id="editResourceCategory"
                        name="category_id"
                        required
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none cursor-pointer"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                    <div
                        id="editCategoryColorPreview"
                        class="mt-2 hidden rounded-xl border px-3 py-2 text-xs font-semibold"
                        aria-live="polite"
                    ></div>
                </div>

                {{-- Homework Deadline --}}
                <div id="editDeadlineGroup" class="mb-4 hidden">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Homework Deadline
                    </label>
                    <input
                        id="editResourceDeadline"
                        type="date"
                        name="deadline"
                        min="{{ now()->toDateString() }}"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label class="mb-2 block text-sm font-medium" style="color: var(--lumina-text-primary);">
                        Description (optional)
                    </label>
                    <textarea
                        id="editResourceDescription"
                        name="description"
                        rows="3"
                        placeholder="Brief description of the resource"
                        class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 resize-none"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        onclick="closeEditResourceModal()"
                        class="flex-1 rounded-xl border px-4 py-3 text-sm font-semibold transition-all hover:bg-gray-50 cursor-pointer"
                        style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-xl px-4 py-3 text-sm font-semibold text-white transition-all hover:opacity-90 cursor-pointer"
                        style="background-color: var(--lumina-primary);"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script for Modal Display --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const uploadModal = document.getElementById('uploadModal');
            const editModal = document.getElementById('editModal');
            const editForm = document.getElementById('editResourceForm');
            const fileInput = document.getElementById('fileInput');
            const fileInputLabel = document.getElementById('fileInputLabel');
            const fileInputHint = document.getElementById('fileInputHint');
            const uploadResourceCategoryInput = document.getElementById('uploadResourceCategory');
            const uploadCategoryColorPreview = document.getElementById('uploadCategoryColorPreview');
            const uploadDeadlineGroup = document.getElementById('uploadDeadlineGroup');
            const uploadDeadlineInput = document.getElementById('uploadDeadlineInput');
            const editResourceIdInput = document.getElementById('editResourceId');
            const editResourceClassInput = document.getElementById('editResourceClass');
            const editResourceNameInput = document.getElementById('editResourceName');
            const editResourceCategoryInput = document.getElementById('editResourceCategory');
            const editCategoryColorPreview = document.getElementById('editCategoryColorPreview');
            const editDeadlineGroup = document.getElementById('editDeadlineGroup');
            const editResourceDeadlineInput = document.getElementById('editResourceDeadline');
            const editResourceDescriptionInput = document.getElementById('editResourceDescription');
            const updateUrlTemplate = editModal.dataset.updateUrlTemplate ?? '';
            const homeworkCategory = @json($homeworkCategory);
            const categoryColorStyles = @json($resourceCategoryStyles);
            const defaultFileLabel = 'Click to select file';
            const defaultFileHint = 'PDF, DOC, DOCX, ZIP up to {{ $maxUploadSizeLabel ?? '50 MB' }}';

            function syncModalDisplay(modal, shouldShow) {
                if (!modal) {
                    return;
                }

                if (shouldShow) {
                    modal.classList.remove('hidden');
                    modal.style.display = 'flex';

                    return;
                }

                modal.classList.add('hidden');
                modal.style.display = 'none';
            }

            function observeModal(modal) {
                if (!modal) {
                    return;
                }

                const observer = new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        if (mutation.attributeName === 'class') {
                            modal.style.display = modal.classList.contains('hidden') ? 'none' : 'flex';
                        }
                    });
                });

                observer.observe(modal, { attributes: true });
            }

            function resetSelectedFileLabel() {
                if (!fileInputLabel || !fileInputHint) {
                    return;
                }

                fileInputLabel.textContent = defaultFileLabel;
                fileInputLabel.style.color = 'var(--lumina-text-muted)';
                fileInputHint.textContent = defaultFileHint;
            }

            function syncSelectedFileLabel() {
                if (!fileInput || !fileInputLabel || !fileInputHint) {
                    return;
                }

                const selectedFile = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;

                if (!selectedFile) {
                    resetSelectedFileLabel();

                    return;
                }

                fileInputLabel.textContent = selectedFile.name;
                fileInputLabel.style.color = 'var(--lumina-text-primary)';
                fileInputHint.textContent = 'Selected file';
            }

            function syncCategoryColorPreview(categoryInput, preview) {
                if (!categoryInput || !preview) {
                    return;
                }

                const categoryStyle = categoryColorStyles[categoryInput.value];

                if (!categoryStyle) {
                    preview.classList.add('hidden');
                    preview.textContent = '';
                    preview.removeAttribute('style');

                    return;
                }

                preview.textContent = categoryStyle.label;
                preview.classList.remove('hidden');
                preview.style.backgroundColor = categoryStyle.background;
                preview.style.borderColor = categoryStyle.border;
                preview.style.color = categoryStyle.text;
            }

            function syncDeadlineField(categoryInput, deadlineGroup, deadlineInput, shouldClear) {
                if (!categoryInput || !deadlineGroup || !deadlineInput) {
                    return;
                }

                const isHomework = categoryInput.value === homeworkCategory;

                deadlineGroup.classList.toggle('hidden', !isHomework);
                deadlineInput.required = isHomework;

                if (!isHomework && shouldClear) {
                    deadlineInput.value = '';
                }
            }

            function fillEditForm(resource) {
                if (!resource || !resource.id) {
                    return;
                }

                editForm.action = updateUrlTemplate.replace('__RESOURCE__', String(resource.id));
                editResourceIdInput.value = String(resource.id);
                editResourceClassInput.value = String(resource.class_id ?? '');
                editResourceNameInput.value = String(resource.name ?? '');
                editResourceCategoryInput.value = String(resource.category_id ?? '');
                editResourceDeadlineInput.value = String(resource.deadline ?? '');
                editResourceDescriptionInput.value = String(resource.description ?? '');
                syncCategoryColorPreview(editResourceCategoryInput, editCategoryColorPreview);
                syncDeadlineField(editResourceCategoryInput, editDeadlineGroup, editResourceDeadlineInput, false);
            }

            window.openEditResourceModal = function (button) {
                const payload = {
                    id: button.dataset.resourceId,
                    class_id: button.dataset.resourceClassId,
                    name: button.dataset.resourceName,
                    category_id: button.dataset.resourceCategoryId,
                    deadline: button.dataset.resourceDeadline,
                    description: button.dataset.resourceDescription,
                };

                fillEditForm(payload);
                syncModalDisplay(editModal, true);
            };

            window.closeEditResourceModal = function () {
                syncModalDisplay(editModal, false);
            };

            syncModalDisplay(uploadModal, uploadModal.dataset.openOnLoad === '1');
            syncModalDisplay(editModal, false);
            observeModal(uploadModal);
            observeModal(editModal);
            syncSelectedFileLabel();
            syncCategoryColorPreview(uploadResourceCategoryInput, uploadCategoryColorPreview);
            syncCategoryColorPreview(editResourceCategoryInput, editCategoryColorPreview);
            syncDeadlineField(uploadResourceCategoryInput, uploadDeadlineGroup, uploadDeadlineInput, true);
            syncDeadlineField(editResourceCategoryInput, editDeadlineGroup, editResourceDeadlineInput, true);

            fileInput?.addEventListener('change', syncSelectedFileLabel);
            uploadResourceCategoryInput?.addEventListener('change', function () {
                syncCategoryColorPreview(uploadResourceCategoryInput, uploadCategoryColorPreview);
                syncDeadlineField(uploadResourceCategoryInput, uploadDeadlineGroup, uploadDeadlineInput, true);
            });
            editResourceCategoryInput?.addEventListener('change', function () {
                syncCategoryColorPreview(editResourceCategoryInput, editCategoryColorPreview);
                syncDeadlineField(editResourceCategoryInput, editDeadlineGroup, editResourceDeadlineInput, true);
            });

            if (editModal.dataset.openOnLoad === '1') {
                const oldResource = {
                    id: editModal.dataset.oldResourceId,
                    class_id: editModal.dataset.oldResourceClassId,
                    name: editModal.dataset.oldResourceName,
                    category_id: editModal.dataset.oldResourceCategoryId,
                    deadline: editModal.dataset.oldResourceDeadline,
                    description: editModal.dataset.oldResourceDescription,
                };

                fillEditForm(oldResource);
                syncModalDisplay(editModal, true);
            }
        });
    </script>
</x-layouts.teacher>
