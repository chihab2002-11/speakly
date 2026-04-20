<x-dynamic-component :component="$layoutComponent ?? 'layouts.student'" :title="__('Learning Materials')" :currentRoute="$currentRoute ?? 'materials'" :pageTitle="'Child Learning Materials'" :user="$user ?? null" :portalParent="$portalParent ?? null" :portalChildren="$portalChildren ?? []" :portalSelectedChild="$portalSelectedChild ?? null">
    <style>
        .mat-main-card { background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 4px rgba(0, 0, 0, .07); overflow: hidden; }
        .mat-header { padding: 20px 24px 16px; border-bottom: 1px solid #f3f4f6; display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .mat-header h2 { font-size: 20px; font-weight: 800; color: #1f2937; }
        .mat-header p { font-size: 13px; color: #6b7280; margin-top: 3px; font-weight: 500; }
        .mat-summary { display: flex; gap: 8px; flex-wrap: wrap; }
        .mat-pill { display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .mat-pill-dot { width: 8px; height: 8px; border-radius: 50%; }
        .mat-pill.total { background: #f3f4f6; color: #374151; }
        .mat-pill.total .mat-pill-dot { background: #9ca3af; }
        .mat-pill.hw { background: #fef3c7; color: #92400e; }
        .mat-pill.hw .mat-pill-dot { background: #f59e0b; }
        .mat-pill.course { background: #dbeafe; color: #1e40af; }
        .mat-pill.course .mat-pill-dot { background: #3b82f6; }
        .mat-filter-bar { padding: 12px 24px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .mat-filter-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; margin-right: 2px; }
        .mat-filter-select { appearance: none; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 6px 26px 6px 10px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 8px center; }
        .mat-filter-select:focus { outline: none; border-color: #2e8b6a; box-shadow: 0 0 0 3px rgba(46, 139, 106, .12); }
        .mat-search-wrap { margin-left: auto; position: relative; }
        .mat-search-wrap svg { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; stroke: #9ca3af; fill: none; stroke-width: 2; }
        .mat-search-input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 6px 12px 6px 30px; font-size: 12px; font-weight: 500; color: #374151; width: 190px; background: #fff; }
        .mat-search-input:focus { outline: none; border-color: #2e8b6a; box-shadow: 0 0 0 3px rgba(46, 139, 106, .12); }
        .mat-grid { padding: 18px 24px 24px; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 14px; }
        .mat-section-row { grid-column: 1 / -1; display: flex; align-items: center; gap: 10px; padding: 4px 0 2px; }
        .mat-section-row span { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #9ca3af; white-space: nowrap; }
        .mat-section-row .line { flex: 1; height: 1px; background: #e5e7eb; }
        .mat-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #fff; display: flex; flex-direction: column; overflow: hidden; transition: box-shadow .2s, transform .15s; }
        .mat-card:hover { box-shadow: 0 6px 20px rgba(0, 0, 0, .1); transform: translateY(-2px); }
        .mat-stripe { height: 4px; }
        .mat-card.pdf .mat-stripe { background: #ef4444; }
        .mat-card.doc .mat-stripe { background: #3b82f6; }
        .mat-card.docx .mat-stripe { background: #3aaa80; }
        .mat-card.zip .mat-stripe { background: #f59e0b; }
        .mat-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; }
        .mat-top { display: flex; align-items: flex-start; gap: 12px; }
        .mat-file-icon { width: 40px; height: 46px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 9px; font-weight: 800; letter-spacing: .04em; gap: 2px; }
        .mat-card.pdf .mat-file-icon { background: #fee2e2; color: #991b1b; }
        .mat-card.doc .mat-file-icon { background: #dbeafe; color: #1e40af; }
        .mat-card.docx .mat-file-icon { background: #d4ede4; color: #1a6b4a; }
        .mat-card.zip .mat-file-icon { background: #fef3c7; color: #92400e; }
        .mat-title { font-size: 13px; font-weight: 700; color: #1f2937; line-height: 1.35; }
        .mat-meta { font-size: 11px; color: #9ca3af; font-weight: 500; margin-top: 3px; }
        .mat-description { font-size: 12px; color: #6b7280; line-height: 1.4; }
        .mat-tags { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .mat-tag { font-size: 10px; font-weight: 700; border-radius: 999px; padding: 2px 8px; }
        .mat-tag.class { background: #eaf5f0; color: #1a6b4a; }
        .mat-tag.hw { background: #fef3c7; color: #92400e; }
        .mat-tag.course { background: #dbeafe; color: #1e40af; }
        .mat-detail { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #9ca3af; font-weight: 500; flex-wrap: wrap; }
        .mat-detail .dot { width: 3px; height: 3px; border-radius: 50%; background: #d1d5db; }
        .mat-actions { padding: 10px 16px 14px; display: flex; gap: 8px; }
        .mat-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; border: none; flex: 1; transition: background .18s, transform .13s; }
        .mat-btn:hover { transform: translateY(-1px); }
        .mat-btn.download { background: #2e8b6a; color: #fff; }
        .mat-btn.download:hover { background: #1a6b4a; }
        .mat-btn.print { background: #f3f4f6; color: #374151; border: 1.5px solid #e5e7eb; }
        .mat-btn.print:hover { background: #e5e7eb; }
        .mat-empty { grid-column: 1 / -1; padding: 48px; text-align: center; color: #9ca3af; font-size: 14px; font-weight: 500; }
    </style>

    <div class="mb-8 flex flex-col gap-2">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">Learning Materials</h1>
        <p class="text-lg" style="color: var(--lumina-text-secondary);">Download homework, course files, and class resources.</p>
    </div>

    <div class="mat-main-card">
        <div class="mat-header">
            <div>
                <h2>Learning Materials</h2>
                <p>Teacher uploaded resources for your classes.</p>
            </div>
            <div class="mat-summary" id="summaryPills"></div>
        </div>

        <div class="mat-filter-bar">
            <span class="mat-filter-label">Filter</span>

            <select class="mat-filter-select" id="classFilter" onchange="renderMaterials()">
                <option value="all">All Classes</option>
            </select>

            <select class="mat-filter-select" id="typeFilter" onchange="renderMaterials()">
                <option value="all">All Types</option>
                <option value="pdf">PDF</option>
                <option value="doc">DOC</option>
                <option value="docx">DOCX</option>
                <option value="zip">ZIP</option>
            </select>

            <select class="mat-filter-select" id="catFilter" onchange="renderMaterials()">
                <option value="all">Homework &amp; Materials</option>
                <option value="homework">Homework only</option>
                <option value="course">Course Material only</option>
            </select>

            <select class="mat-filter-select" id="sortFilter" onchange="renderMaterials()">
                <option value="recent">Most Recent</option>
                <option value="az">A - Z</option>
                <option value="size">By Size</option>
            </select>

            <div class="mat-search-wrap">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input class="mat-search-input" id="searchInput" type="text" placeholder="Search files..." oninput="renderMaterials()">
            </div>
        </div>

        <div class="mat-grid" id="materialsGrid"></div>
    </div>

    <textarea id="studentMaterialsData" hidden>{{ base64_encode(json_encode($materials ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)) }}</textarea>
    <script>
        const MATERIALS = (() => {
            const payload = document.getElementById('studentMaterialsData');

            if (!payload) {
                return [];
            }

            try {
                const encoded = (payload.value || payload.textContent || '').trim();
                const decoded = encoded ? atob(encoded) : '[]';
                const parsed = JSON.parse(decoded);
                return Array.isArray(parsed) ? parsed : [];
            } catch (error) {
                return [];
            }
        })();
        const MATERIALS_SEEN_STORAGE_KEY = 'student-materials-last-seen-upload-ts';

        const FILE_ICON = {
            pdf: '<span>PDF</span>',
            doc: '<span>DOC</span>',
            docx: '<span>DOCX</span>',
            zip: '<span>ZIP</span>'
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openPrintForMaterial(url) {
            if (!url) {
                return;
            }

            const printTab = window.open(url, '_blank', 'noopener');

            if (!printTab) {
                return;
            }

            const triggerPrint = () => {
                try {
                    printTab.focus();
                    printTab.print();
                } catch (error) {
                    // If browser blocks scripted print in embedded viewers, file remains open for manual print.
                }
            };

            if (typeof printTab.addEventListener === 'function') {
                printTab.addEventListener('load', () => setTimeout(triggerPrint, 350), { once: true });
            }

            setTimeout(triggerPrint, 1400);
        }

        function formatSize(mb) {
            const numericMb = Number(mb || 0);

            if (!Number.isFinite(numericMb) || numericMb <= 0) {
                return '0 KB';
            }

            mb = numericMb;

            return mb >= 1 ? mb.toFixed(1) + ' MB' : Math.round(mb * 1000) + ' KB';
        }

        function timeAgo(timestamp) {
            const epoch = typeof timestamp === 'number' ? timestamp : Date.parse(String(timestamp || ''));
            const base = Number.isNaN(epoch) ? Date.now() : epoch;
            const seconds = Math.max(1, Math.floor((Date.now() - base) / 1000));

            if (seconds < 60) return seconds + 's ago';

            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + ' min ago';

            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h ago';

            const days = Math.floor(hours / 24);
            return days + ' day' + (days > 1 ? 's' : '') + ' ago';
        }

        function fillClassFilter() {
            const classFilter = document.getElementById('classFilter');
            const classes = [...new Set(MATERIALS.map(item => String(item.className || '')))].filter(Boolean);

            classFilter.innerHTML = '<option value="all">All Classes</option>';
            classes.forEach(name => {
                const option = document.createElement('option');
                option.value = name;
                option.textContent = name;
                classFilter.appendChild(option);
            });
        }

        function renderMaterials() {
            const classFilter = document.getElementById('classFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const catFilter = document.getElementById('catFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;
            const search = document.getElementById('searchInput').value.toLowerCase();

            let list = MATERIALS.filter(item => {
                const className = String(item.className || '');
                const type = String(item.type || '').toLowerCase();
                const category = String(item.category || '');
                const resourceName = String(item.resourceName || '');
                const description = String(item.description || '');

                return (classFilter === 'all' || className === classFilter)
                    && (typeFilter === 'all' || type === typeFilter)
                    && (catFilter === 'all' || category === catFilter)
                    && (!search || resourceName.toLowerCase().includes(search) || className.toLowerCase().includes(search) || description.toLowerCase().includes(search));
            });

            if (sortFilter === 'az') list.sort((a, b) => a.resourceName.localeCompare(b.resourceName));
            else if (sortFilter === 'size') list.sort((a, b) => b.sizeMb - a.sizeMb);
            else list.sort((a, b) => Date.parse(String(b.uploadedAt || '')) - Date.parse(String(a.uploadedAt || '')));

            const total = MATERIALS.length;
            const hwCount = MATERIALS.filter(item => item.category === 'homework').length;
            const courseCount = MATERIALS.filter(item => item.category === 'course').length;

            document.getElementById('summaryPills').innerHTML = `
                <div class="mat-pill total"><span class="mat-pill-dot"></span>${total} Files</div>
                <div class="mat-pill hw"><span class="mat-pill-dot"></span>${hwCount} Homework</div>
                <div class="mat-pill course"><span class="mat-pill-dot"></span>${courseCount} Materials</div>
            `;

            const grouped = {};
            list.forEach(item => {
                if (!grouped[item.className]) grouped[item.className] = [];
                grouped[item.className].push(item);
            });

            const grid = document.getElementById('materialsGrid');

            if (!list.length) {
                grid.innerHTML = '<div class="mat-empty">No files match your filters.</div>';
                return;
            }

            grid.innerHTML = Object.entries(grouped).map(([className, items]) => `
                <div class="mat-section-row"><span>${className}</span><div class="line"></div></div>
                ${items.map(item => {
                    const type = ['pdf', 'doc', 'docx', 'zip'].includes(String(item.type || '').toLowerCase())
                        ? String(item.type).toLowerCase()
                        : 'pdf';

                    return `
                    <div class="mat-card ${type}">
                        <div class="mat-stripe"></div>
                        <div class="mat-body">
                            <div class="mat-top">
                                <div class="mat-file-icon">${FILE_ICON[type] || escapeHtml(type.toUpperCase())}</div>
                                <div>
                                    <div class="mat-title">${escapeHtml(item.resourceName)}</div>
                                    <div class="mat-meta">Uploaded by ${escapeHtml(item.teacher)}</div>
                                </div>
                            </div>
                            <div class="mat-description">${escapeHtml(item.description)}</div>
                            <div class="mat-tags">
                                <span class="mat-tag class">${escapeHtml(item.className)}</span>
                                <span class="mat-tag ${item.category === 'homework' ? 'hw' : 'course'}">${item.category === 'homework' ? 'Homework' : 'Course Material'}</span>
                            </div>
                            <div class="mat-detail">
                                <span>${timeAgo(item.uploadedAt)}</span>
                                <span class="dot"></span>
                                <span>${formatSize(item.sizeMb)}</span>
                                <span class="dot"></span>
                                <span>${escapeHtml(type.toUpperCase())}</span>
                            </div>
                        </div>
                        <div class="mat-actions">
                            <a class="mat-btn download" href="${escapeHtml(item.downloadUrl)}">Download</a>
                            <button class="mat-btn print js-print-material" type="button" data-print-url="${escapeHtml(item.printUrl)}">Print</button>
                        </div>
                    </div>
                `; }).join('')}
            `).join('');

            grid.querySelectorAll('.js-print-material').forEach(button => {
                button.addEventListener('click', () => {
                    openPrintForMaterial(button.dataset.printUrl || '');
                });
            });
        }

        function markMaterialsAsSeen() {
            const latestUploadTs = MATERIALS.reduce((latest, item) => {
                const ts = Date.parse(String(item.uploadedAt || ''));
                return Number.isNaN(ts) ? latest : Math.max(latest, ts);
            }, 0);

            if (latestUploadTs > 0) {
                const seenTs = Number(localStorage.getItem(MATERIALS_SEEN_STORAGE_KEY) || 0);

                if (latestUploadTs > seenTs) {
                    localStorage.setItem(MATERIALS_SEEN_STORAGE_KEY, String(latestUploadTs));
                }
            }

            window.dispatchEvent(new Event('student-materials-badge:update'));
        }

        fillClassFilter();
        renderMaterials();
        markMaterialsAsSeen();
    </script>
</x-dynamic-component>
