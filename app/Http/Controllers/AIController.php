<?php

namespace App\Http\Controllers;

use App\Services\AI\AIInsightService;

class AIController extends Controller
{

    public function index()
    {
        return view('ai.index');
    }

    public function businessAnalysis()
    {
        $summary = AIInsightService::getBusinessSummary();
        $insight = AIInsightService::generateInsight();

        return view('ai.business-analysis', compact('summary','insight'));
    }

}