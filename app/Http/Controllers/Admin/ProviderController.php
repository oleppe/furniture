<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{
    /**
     * Display the AI model provider directory with model counts and metadata.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();

        $query = Provider::withCount('models');

        if ($search->isNotEmpty()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%");
            });
        }

        $providers = $query->orderByDesc('models_count')
            ->paginate(24)
            ->withQueryString();

        $totalProviders = Provider::count();

        return view('admin.providers.index', compact('providers', 'totalProviders', 'search'));
    }
}
