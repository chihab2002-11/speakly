<?php

namespace App\Http\Controllers;

use App\Support\TuitionFinancialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentFinancialController extends Controller
{
    public function __construct(private TuitionFinancialService $tuitionFinancialService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('parent.financial', array_merge(
            ['user' => $user],
            $this->tuitionFinancialService->buildParentPageData($user)
        ));
    }
}
