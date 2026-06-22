<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Pricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Лёгкие JSON-эндпоинты для клиентского экранного стека витрины.
 * Грузятся по требованию, без перезагрузки Inertia-страницы.
 */
class ShopApiController extends Controller
{
    /** Товары категории (для экрана категории и переключения чипов). */
    public function products(Category $category): JsonResponse
    {
        if (! $category->is_active) {
            abort(404);
        }

        $products = $category->products()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'category' => [
                'id'             => $category->id,
                'name'           => $category->name,
                'show_price'     => $category->show_price,
                'products_count' => $products->count(),
            ],
            'products' => $products->map(
                fn (Product $p) => $this->productCard($p, $category->show_price)
            )->all(),
        ]);
    }

    /** Все активные товары (чип «Все» на экране категории). */
    public function all(): JsonResponse
    {
        $products = Product::with('category:id,show_price')
            ->where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'category' => null,
            'products' => $products->map(
                fn (Product $p) => $this->productCard($p, $p->category?->show_price ?? true)
            )->all(),
        ]);
    }

    /**
     * Товары по списку id — для экрана «Поделились избранным».
     * Порядок ответа сохраняет порядок переданных id.
     */
    public function favorites(Request $request): JsonResponse
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(100)
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['products' => []]);
        }

        $products = Product::with('category:id,show_price')
            ->where('is_active', true)
            ->whereIn('id', $ids->all())
            ->get()
            ->sortBy(fn (Product $p) => $ids->search($p->id))
            ->values();

        return response()->json([
            'products' => $products->map(
                fn (Product $p) => $this->productCard($p, $p->category?->show_price ?? true)
            )->all(),
        ]);
    }

    /** Один товар: изображения, варианты, описание. */
    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        $product->loadMissing(['variants', 'category']);

        // Инкремент просмотров (открытие карточки товара).
        $product->increment('views');

        $showPrice = $product->category?->show_price ?? true;

        return response()->json([
            'id'          => $product->id,
            'name'        => $product->name,
            'description' => $product->description,
            'is_new'      => $product->is_new,
            'show_price'  => $showPrice,
            'images'      => $product->image_urls,
            'price'       => $showPrice ? Pricing::rawFormatted($product->price, $product->currency) : null,
            'variants'    => $showPrice
                ? $product->variants->map(fn (ProductVariant $v) => [
                    'id'    => $v->id,
                    'name'  => $v->name,
                    'price' => Pricing::rawFormatted($v->effective_price, $v->effective_currency),
                ])->all()
                : [],
        ]);
    }

    /** Все изображения активных товаров — для экрана «Галерея». */
    public function gallery(): JsonResponse
    {
        $images = [];

        Product::where('is_active', true)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'images'])
            ->each(function (Product $p) use (&$images) {
                foreach ($p->image_urls as $url) {
                    $images[] = [
                        'url'          => $url,
                        'product_id'   => $p->id,
                        'product_name' => $p->name,
                    ];
                }
            });

        return response()->json(['images' => $images]);
    }

    /** Поиск товаров по названию и вариантам. */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->string('q'));

        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'products' => []]);
        }

        $products = Product::with('category:id,show_price')
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhereHas('variants', fn ($vq) => $vq->where('name', 'like', "%{$q}%"));
            })
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        return response()->json([
            'query'    => $q,
            'products' => $products->map(function (Product $p) {
                $showPrice = $p->category?->show_price ?? true;
                $images    = $p->image_urls;
                $active    = $p->active_image ?? 0;

                return [
                    'id'     => $p->id,
                    'name'   => $p->name,
                    'is_new' => $p->is_new,
                    'image'  => $images[$active] ?? $images[0] ?? null,
                    'price'  => $showPrice ? Pricing::rawFormatted($p->price, $p->currency) : null,
                ];
            })->all(),
        ]);
    }

    /** Трекинг визита в activity_logs (читает админка). */
    public function track(Request $request): JsonResponse
    {
        $data = $request->validate([
            'session'    => ['nullable', 'string', 'regex:/^[a-f0-9]{8}$/'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        ActivityLog::create([
            'session'    => $data['session'] ?? substr(bin2hex(random_bytes(4)), 0, 8),
            'product_id' => $data['product_id'] ?? null,
            'region'     => 'Aşgabat',
            'device'     => $this->detectDevice($request->userAgent()),
            'brand'      => $this->detectBrand($request->userAgent()),
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /** @return array<string, mixed> */
    private function productCard(Product $product, bool $showPrice): array
    {
        $images = $product->image_urls;
        $active = $product->active_image ?? 0;

        return [
            'id'     => $product->id,
            'name'   => $product->name,
            'is_new' => $product->is_new,
            'image'  => $images[$active] ?? $images[0] ?? null,
            'price'  => $showPrice ? Pricing::rawFormatted($product->price, $product->currency) : null,
        ];
    }

    private function detectDevice(?string $ua): string
    {
        $ua = strtolower((string) $ua);

        if (str_contains($ua, 'ipad') || (str_contains($ua, 'android') && ! str_contains($ua, 'mobile'))) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobile') || str_contains($ua, 'iphone') || str_contains($ua, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrand(?string $ua): ?string
    {
        $ua = (string) $ua;

        return match (true) {
            str_contains($ua, 'iPhone')              => 'iPhone',
            str_contains($ua, 'iPad')                => 'iPad',
            str_contains($ua, 'Android')             => 'Android',
            str_contains($ua, 'Windows')             => 'Windows',
            str_contains($ua, 'Macintosh')           => 'Mac',
            str_contains($ua, 'Linux')               => 'Linux',
            default                                  => null,
        };
    }
}
