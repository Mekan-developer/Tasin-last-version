<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Repositories\ProductRepository;
use App\Traits\ConvertsToWebp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use ConvertsToWebp;
    public function __construct(private ProductRepository $repo) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'category_id', 'is_active', 'is_new']);
        return Inertia::render('Products/Index', [
            'products'   => $this->repo->paginate($filters),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'filters'    => $filters,
            'tmtRate'    => (float) (Setting::find('tmt_rate')?->value ?? 19.50),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data     = $request->safe()->except(['variants', 'images_upload']);
        $variants = $request->validated()['variants'] ?? [];

        $images = [];
        foreach ($request->file('images_upload', []) as $file) {
            $images[] = $this->storeAsWebp($file, 'products');
        }
        $data['images'] = $images;

        $this->repo->create($data, $variants);
        return back()->with('success', 'Товар добавлен.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data     = $request->safe()->except(['variants', 'images_upload']);
        $variants = $request->input('variants');

        $images = $product->images ?? [];
        foreach ($request->file('images_upload', []) as $file) {
            $images[] = $this->storeAsWebp($file, 'products');
        }
        $data['images'] = $images;

        $this->repo->update($product, $data, $variants);
        return back()->with('success', 'Товар обновлён.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (! empty($product->images)) {
            Storage::disk('public')->delete($product->images);
        }
        $this->repo->delete($product);
        return back()->with('success', 'Товар удалён.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);
        return back();
    }

    public function updateVariants(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'variants'            => 'present|array',
            'variants.*.name'     => 'required|string|max:255',
            'variants.*.price'    => 'nullable|numeric|min:0',
            'variants.*.currency' => 'nullable|in:USD,TMT',
        ], [
            'variants.*.name.required' => 'Wariant ady hökmany.',
            'variants.*.name.max'      => 'Wariant ady 255 simvoldan uzyn bolmaly däldir.',
            'variants.*.price.numeric' => 'Wariant bahasy san bolmaly.',
            'variants.*.price.min'     => 'Wariant bahasy 0-dan kiçi bolmaly däldir.',
            'variants.*.currency.in'   => 'Wariant walýutasy diňe USD ýa-da TMT bolup biler.',
        ]);

        $this->repo->syncVariants($product, $data['variants']);

        return back()->with('success', 'Варианты обновлены.');
    }

    public function sortData(Request $request): \Illuminate\Http\JsonResponse
    {
        $categoryId = $request->integer('category_id');
        if (!$categoryId) {
            return response()->json([]);
        }
        $products = Product::where('category_id', $categoryId)
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'images', 'order']);

        return response()->json($products->each->append('image_urls'));
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'items'          => 'required|array',
            'items.*.id'    => 'required|integer|exists:products,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $item) {
            Product::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['ok' => true]);
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'products'                       => 'required|array|min:1',
            'products.*.name'                => 'required|string|max:255',
            'products.*.category_id'         => 'required|exists:categories,id',
            'products.*.price'               => 'required|numeric|min:0',
            'products.*.currency'            => 'nullable|in:USD,TMT',
            'products.*.is_new'              => 'nullable|boolean',
            'products.*.is_active'           => 'nullable|boolean',
            'products.*.image'               => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'products.*.variants'            => 'nullable|array',
            'products.*.variants.*.name'     => 'required|string|max:255',
            'products.*.variants.*.price'    => 'nullable|numeric|min:0',
            'products.*.variants.*.currency' => 'nullable|in:USD,TMT',
        ]);

        DB::transaction(function () use ($request, $data) {
            foreach ($data['products'] as $i => $p) {
                $images = [];
                if ($request->hasFile("products.$i.image")) {
                    $images[] = $this->storeAsWebp($request->file("products.$i.image"), 'products');
                }

                $product = Product::create([
                    'name'        => $p['name'],
                    'category_id' => $p['category_id'],
                    'price'       => $p['price'],
                    'currency'    => $p['currency'] ?? 'USD',
                    'is_new'      => (bool)(int)($p['is_new'] ?? 0),
                    'is_active'   => (bool)(int)($p['is_active'] ?? 1),
                    'images'      => $images,
                    'views'       => 0,
                ]);

                foreach ($p['variants'] ?? [] as $v) {
                    $product->variants()->create([
                        'name'     => $v['name'],
                        'price'    => ($v['price'] ?? '') === '' ? null : $v['price'],
                        'currency' => $v['currency'] ?? 'USD',
                    ]);
                }
            }
        });

        return back()->with('success', count($data['products']) . ' товаров успешно добавлено!');
    }
}
