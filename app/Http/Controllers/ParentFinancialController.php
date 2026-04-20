<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TuitionFinancialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentFinancialController extends Controller
{
    public function __construct(private TuitionFinancialService $tuitionFinancialService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $children = User::query()
            ->where('parent_id', $user->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', fn ($query) => $query->where('name', 'student'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values()
            ->map(function (User $child, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'initials' => $child->initials(),
                    'grade' => 'Student',
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->all();

        return view('parent.financial', array_merge(
            ['user' => $user, 'children' => $children],
            $this->tuitionFinancialService->buildParentPageData($user)
        ));
    }
}
