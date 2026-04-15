<?php

namespace App\Http\Controllers;

use App\Support\TuitionFinancialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentFinancialController extends Controller
{
    public function __construct(private TuitionFinancialService $tuitionFinancialService) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('student.financial', array_merge(
            ['user' => $user],
            $this->tuitionFinancialService->buildStudentPageData($user)
        ));
    }
}
