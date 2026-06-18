<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'info'    => fn () => $request->session()->get('info'),
            ],
            // Lazy-evaluated: nav badge counts for sidebar
            'nav' => fn () => [
                'categoriesCount' => Category::count(),
                'productsCount'   => Product::count(),
                'slidesCount'     => Slide::count(),
                'staffCount'      => Schema::hasTable('staff') ? DB::table('staff')->count() : 0,
            ],
        ];
    }
}
