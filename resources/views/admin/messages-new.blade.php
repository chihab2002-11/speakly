<x-layouts.admin :title="__('New Message')">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            New Message
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Select a recipient and start a conversation.
        </p>
    </div>

    {{-- User Selection Card --}}
    <div class="overflow-hidden rounded-3xl border" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
        <div class="border-b px-6 py-4" style="border-color: var(--lumina-border);">
            <h3 class="text-lg font-semibold" style="color: var(--lumina-text-primary);">Select Recipient</h3>
        </div>

        {{-- Filters --}}
        <div class="border-b p-4 space-y-3" style="border-color: var(--lumina-border);">
            {{-- Role Filter --}}
            <div>
                <label for="role-filter" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Filter by Role</label>
                <select 
                    id="role-filter"
                    class="w-full rounded-xl border px-4 py-2 text-sm outline-none transition-all focus:ring-2"
                    style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                >
                    <option value="">All Roles</option>
                    <option value="student">Students</option>
                    <option value="parent">Parents</option>
                    <option value="teacher">Teachers</option>
                    <option value="secretary">Secretaries</option>
                </select>
            </div>

            {{-- Search --}}
            <div>
                <label for="user-search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search by Name or Email</label>
                <div class="flex items-center gap-2 rounded-xl px-4 py-2" style="background-color: var(--lumina-bg-card);">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                    <input 
                        type="text" 
                        id="user-search"
                        placeholder="Search by name or email..." 
                        class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400"
                    >
                </div>
            </div>
        </div>

        {{-- Users List --}}
        <div class="max-h-96 overflow-y-auto" id="users-list">
            @forelse($users as $user)
                <a 
                    href="{{ route('role.messages.conversation', ['role' => 'admin', 'conversation' => $user->id]) }}"
                    class="user-item flex items-center gap-4 border-b p-4 transition-colors hover:bg-gray-50"
                    style="border-color: var(--lumina-border);"
                    data-name="{{ strtolower($user->name) }}"
                    data-email="{{ strtolower($user->email) }}"
                    data-role="{{ $user->roles->isNotEmpty() ? strtolower($user->roles->first()->name) : '' }}"
                >
                    {{-- Avatar --}}
                    <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ 
                        $user->roles->isNotEmpty() && $user->roles->first()->name === 'student' ? 'bg-blue-100' :
                        ($user->roles->isNotEmpty() && $user->roles->first()->name === 'parent' ? 'bg-purple-100' :
                        ($user->roles->isNotEmpty() && $user->roles->first()->name === 'teacher' ? 'bg-green-100' : 'bg-orange-100'))
                    }}">
                        <span class="text-sm font-bold {{ 
                            $user->roles->isNotEmpty() && $user->roles->first()->name === 'student' ? 'text-blue-700' :
                            ($user->roles->isNotEmpty() && $user->roles->first()->name === 'parent' ? 'text-purple-700' :
                            ($user->roles->isNotEmpty() && $user->roles->first()->name === 'teacher' ? 'text-green-700' : 'text-orange-700'))
                        }}">
                            {{ substr($user->name, 0, 1) }}
                        </span>
                    </div>

                    {{-- User Info --}}
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                            {{ $user->name }}
                        </h4>
                        <p class="text-xs" style="color: var(--lumina-text-muted);">
                            {{ $user->email }}
                            @if($user->roles->isNotEmpty())
                                • <span class="role-badge inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ 
                                    $user->roles->first()->name === 'student' ? 'bg-blue-50 text-blue-700' :
                                    ($user->roles->first()->name === 'parent' ? 'bg-purple-50 text-purple-700' :
                                    ($user->roles->first()->name === 'teacher' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-700'))
                                }}">
                                    {{ ucfirst($user->roles->first()->name) }}
                                </span>
                            @endif
                        </p>
                    </div>

                    {{-- Arrow Icon --}}
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <p class="text-sm" style="color: var(--lumina-text-muted);">No users available</p>
                </div>
            @endforelse
        </div>

        {{-- No Results Message --}}
        <div id="no-results" class="hidden p-8 text-center">
            <div class="mb-3 flex justify-center">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">No users found</p>
            <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Try adjusting your filters or search query</p>
        </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-6">
        <a 
            href="{{ route('role.messages.index', ['role' => 'admin']) }}"
            class="inline-flex items-center gap-2 text-sm font-semibold transition-colors hover:opacity-80"
            style="color: var(--lumina-primary);"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Messages
        </a>
    </div>

    {{-- Filter and Search functionality --}}
    <script>
        const searchInput = document.getElementById('user-search');
        const roleFilter = document.getElementById('role-filter');
        const userItems = document.querySelectorAll('.user-item');
        const usersList = document.getElementById('users-list');
        const noResults = document.getElementById('no-results');

        function filterUsers() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedRole = roleFilter.value.toLowerCase();
            let visibleCount = 0;

            userItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const email = item.getAttribute('data-email');
                const role = item.getAttribute('data-role');
                
                // Check if matches search term (name or email)
                const matchesSearch = searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm);
                
                // Check if matches selected role
                const matchesRole = selectedRole === '' || role === selectedRole;
                
                // Show item if it matches both filters
                if (matchesSearch && matchesRole) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide "no results" message
            if (visibleCount === 0) {
                usersList.classList.add('hidden');
                noResults.classList.remove('hidden');
            } else {
                usersList.classList.remove('hidden');
                noResults.classList.add('hidden');
            }
        }

        // Add event listeners
        searchInput.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);
    </script>
</x-layouts.admin>
