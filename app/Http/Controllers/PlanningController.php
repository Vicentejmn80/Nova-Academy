<?php

namespace App\Http\Controllers;

use App\Models\Planificacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlanningController extends Controller
{
    public function index(): View
    {
        $plans = Planificacion::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('planning.index', compact('plans'));
    }
}

