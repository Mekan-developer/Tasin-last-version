<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Slide;
use App\Support\Pricing;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Публичная витрина (storefront). Отдаёт оболочку-приложение Shop/Index
 * с начальными данными. Никакой авторизации — только чтение.
 */
class ShopController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Shop/Index', [
            'settings'   => $this->settings(),
            'slides'     => $this->slides(),
            'categories' => $this->categories(),
        ]);
    }

    private function settings(): array
    {
        return [
            'market_name' => Setting::get('market_name', 'Tasin Mobil'),
            'phone'       => Setting::get('phone', '+993 12 34-56-78'),
            'address'     => Setting::get('address', 'г. Ашхабад'),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function slides(): array
    {
        return Slide::orderBy('order')
            ->orderBy('id')
            ->get()
            ->map(fn (Slide $s) => [
                'id'             => $s->id,
                'badge'          => $s->badge,
                'title'          => $s->title,
                'description'    => $s->description,
                'bg_color'       => $s->bg_color,
                'image_url'      => $s->image_url,
                'price'          => $s->price ? Pricing::rawFormatted($s->price, $s->currency) : null,
                'old_price'      => $s->old_price ? Pricing::rawFormatted($s->old_price, $s->currency) : null,
                'discount'       => $s->discount,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function categories(): array
    {
        return Category::where('is_active', true)
            ->withCount('activeProducts')
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'image_url'      => $c->image_url,
                'show_price'     => $c->show_price,
                'products_count' => $c->active_products_count,
            ])
            ->all();
    }
}
