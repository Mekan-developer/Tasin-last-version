<?php

namespace App\Repositories;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slide;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardRepository
{
    public function stats(): array
    {
        return [
            'categoriesCount'    => Category::count(),
            'activeCategories'   => Category::where('is_active', true)->count(),
            'productsCount'      => Product::count(),
            'newProductsCount'   => Product::where('is_new', true)->count(),
            'slidesCount'        => Slide::count(),
            'discountSlidesCount'=> Slide::whereNotNull('discount')->where('discount', '>', 0)->count(),
            'adminCount'         => AdminUser::count(),
            'adminRoles'         => [
                'admin'   => AdminUser::where('role', 'admin')->count(),
                'manager' => AdminUser::where('role', 'manager')->count(),
            ],
        ];
    }

    public function topProducts(int $limit = 6): Collection
    {
        $items = Product::with('category')
            ->orderByDesc('views')
            ->limit($limit)
            ->get(['id', 'name', 'views', 'category_id']);

        $maxViews = $items->max('views') ?: 1;

        return $items->map(fn ($p) => [
            'id'       => $p->id,
            'name'     => $p->name,
            'category' => $p->category?->name ?? '—',
            'views'    => $p->views,
            'barWidth' => (int) round(($p->views / $maxViews) * 100),
        ]);
    }

    public function recentActivity(int $limit = 8): Collection
    {
        $items = collect();

        Product::with('category')->latest()->limit(5)
            ->get(['id', 'name', 'created_at'])
            ->each(fn ($p) => $items->push([
                'iconType'  => 'box',
                'prefix'    => 'Добавлен товар',
                'highlight' => $p->name,
                'time'      => $p->created_at->locale('ru')->diffForHumans(),
                'timestamp' => $p->created_at->timestamp,
                'color'     => 'blue',
            ]));

        Category::latest()->limit(4)
            ->get(['id', 'name', 'created_at'])
            ->each(fn ($c) => $items->push([
                'iconType'  => 'folder',
                'prefix'    => 'Добавлена категория',
                'highlight' => $c->name,
                'time'      => $c->created_at->locale('ru')->diffForHumans(),
                'timestamp' => $c->created_at->timestamp,
                'color'     => 'green',
            ]));

        Slide::latest()->limit(3)
            ->get(['id', 'title', 'created_at'])
            ->each(fn ($s) => $items->push([
                'iconType'  => 'slide',
                'prefix'    => 'Добавлен слайд',
                'highlight' => $s->title,
                'time'      => $s->created_at->locale('ru')->diffForHumans(),
                'timestamp' => $s->created_at->timestamp,
                'color'     => 'orange',
            ]));

        return $items->sortByDesc('timestamp')->values()->take($limit);
    }

    public function settings(): array
    {
        return [
            'tmt_rate'    => (float) (Setting::find('tmt_rate')?->value ?? 19.50),
            'markup'      => (int) (Setting::find('markup')?->value ?? 15),
            'market_name' => Setting::find('market_name')?->value ?? 'Tasin Mobil',
        ];
    }
}
