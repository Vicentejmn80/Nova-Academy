<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Services\DirectorAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DirectorAnalyticsService $analytics) {}

    public function index(Request $request): View
    {
        $filterGrade   = $request->query('grade');
        $filterSection = $request->query('section');

        $this->analytics->withFilters($filterGrade, $filterSection);

        $stats        = $this->analytics->getStats();
        $courseChart  = $this->analytics->getAveragePerCourse();
        $ranking      = $this->analytics->getTopAndBottomStudents();
        $distribution = $this->analytics->getGradeDistribution();
        $atRisk       = $this->analytics->getAtRiskStudents();
        $filters      = $this->analytics->getAvailableFilters();

        $user         = auth()->user();
        $settings     = $user->settings;

        return view('director.dashboard', compact(
            'stats', 'courseChart', 'ranking',
            'distribution', 'atRisk', 'filters',
            'user', 'settings',
            'filterGrade', 'filterSection'
        ));
    }
}
