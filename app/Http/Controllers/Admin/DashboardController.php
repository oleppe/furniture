<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiModel;
use App\Models\ModelOffering;
use App\Models\Provider;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main administrative dashboard with high-level intelligence metrics.
     */
    public function index(): View
    {
        $totalModels = AiModel::count();
        $activeModels = AiModel::where('is_active', true)->count();
        $featuredModels = AiModel::where('is_featured', true)->count();
        $totalProviders = Provider::count();
        $totalOfferings = ModelOffering::count();
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();

        // Top 5 providers by hosted models
        $topProviders = Provider::withCount('models')
            ->orderByDesc('models_count')
            ->take(5)
            ->get();

        // Recent 5 models with latest specs
        $recentModels = AiModel::with(['provider', 'latestSpec'])
            ->latest('id')
            ->take(6)
            ->get();

        // Cheapest offerings highlights
        $cheapestOfferings = ModelOffering::with(['aiModel', 'provider'])
            ->where('input_cost_per_1m', '>', 0)
            ->orderBy('input_cost_per_1m')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalModels',
            'activeModels',
            'featuredModels',
            'totalProviders',
            'totalOfferings',
            'totalUsers',
            'adminCount',
            'topProviders',
            'recentModels',
            'cheapestOfferings'
        ));
    }
}
