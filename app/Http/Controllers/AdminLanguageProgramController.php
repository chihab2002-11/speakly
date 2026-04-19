<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderLanguageProgramsRequest;
use App\Http\Requests\StoreLanguageProgramRequest;
use App\Http\Requests\UpdateLanguageProgramRequest;
use App\Models\LanguageProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminLanguageProgramController extends Controller
{
    public function store(StoreLanguageProgramRequest $request): RedirectResponse
    {
        $maxSortOrder = (int) LanguageProgram::query()->max('sort_order');
        $validated = $request->validated();

        LanguageProgram::query()->create([
            ...$validated,
            'code' => strtolower((string) $validated['code']),
            'sort_order' => $maxSortOrder + 1,
            'is_active' => $request->boolean('is_active', true),
            'certifications' => [],
        ]);

        return back()->with('success', 'Program created successfully.');
    }

    public function update(UpdateLanguageProgramRequest $request, LanguageProgram $program): RedirectResponse
    {
        $validated = $request->validated();

        $program->update([
            ...$validated,
            'code' => strtolower((string) $validated['code']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Program updated successfully.');
    }

    public function destroy(LanguageProgram $program): RedirectResponse
    {
        $program->delete();

        $this->normalizeSortOrder();

        return back()->with('success', 'Program deleted successfully.');
    }

    public function toggleStatus(Request $request, LanguageProgram $program): RedirectResponse
    {
        abort_unless($request->user()?->can('language-programs.manage'), 403);

        $program->update([
            'is_active' => ! $program->is_active,
        ]);

        return back()->with('success', 'Program visibility updated.');
    }

    public function reorder(ReorderLanguageProgramsRequest $request): RedirectResponse
    {
        $orderedIds = $request->validated('ordered_ids');

        foreach ($orderedIds as $index => $id) {
            LanguageProgram::query()
                ->whereKey((int) $id)
                ->update(['sort_order' => $index + 1]);
        }

        return back()->with('success', 'Program order updated.');
    }

    public function move(Request $request, LanguageProgram $program, string $direction): RedirectResponse
    {
        abort_unless($request->user()?->can('language-programs.manage'), 403);

        if (! in_array($direction, ['up', 'down'], true)) {
            return back();
        }

        $orderedPrograms = LanguageProgram::query()->ordered()->get();
        $currentIndex = $orderedPrograms->search(fn (LanguageProgram $item): bool => $item->id === $program->id);

        if (! is_int($currentIndex)) {
            return back();
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (! isset($orderedPrograms[$targetIndex])) {
            return back();
        }

        $targetProgram = $orderedPrograms[$targetIndex];

        $currentOrder = $program->sort_order;
        $program->update(['sort_order' => $targetProgram->sort_order]);
        $targetProgram->update(['sort_order' => $currentOrder]);

        $this->normalizeSortOrder();

        return back()->with('success', 'Program order updated.');
    }

    private function normalizeSortOrder(): void
    {
        LanguageProgram::query()
            ->ordered()
            ->get(['id'])
            ->values()
            ->each(function (LanguageProgram $item, int $index): void {
                $item->update(['sort_order' => $index + 1]);
            });
    }
}
