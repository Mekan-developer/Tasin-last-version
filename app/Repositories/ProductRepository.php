<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['category:id,name', 'variants'])
            ->when($filters['search'] ?? '', fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($filters['category_id'] ?? '', fn ($q, $id) => $q->where('category_id', $id))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($q) => $q->where('is_active', (bool) $filters['is_active']))
            ->when(isset($filters['is_new']) && $filters['is_new'] !== '', fn ($q) => $q->where('is_new', (bool) $filters['is_new']))
            ->orderBy('order')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data, array $variants = []): Product
    {
        $product = Product::create($data);
        if ($variants) {
            $product->variants()->createMany(
                array_map([$this, 'normalizeVariant'], $variants)
            );
        }
        return $product->load('variants');
    }

    public function update(Product $product, array $data, ?array $variants = null): void
    {
        $product->update($data);
        if ($variants !== null) {
            $product->variants()->delete();
            if ($variants) {
                $product->variants()->createMany(
                    array_map([$this, 'normalizeVariant'], $variants)
                );
            }
        }
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Replace a product's variants with the given set, leaving the product
     * itself untouched. Used by the variants-only editor.
     */
    public function syncVariants(Product $product, array $variants): void
    {
        $product->variants()->delete();
        if ($variants) {
            $product->variants()->createMany(
                array_map([$this, 'normalizeVariant'], $variants)
            );
        }
    }

    /**
     * Normalize an incoming variant. An empty price is stored as null so the
     * variant inherits the parent product's price.
     */
    public function normalizeVariant(array $v): array
    {
        $price = $v['price'] ?? null;

        return [
            'name'     => $v['name'],
            'price'    => ($price === '' || $price === null) ? null : $price,
            'currency' => $v['currency'] ?? 'USD',
        ];
    }

    public function bulkCreate(array $rows): int
    {
        foreach ($rows as $row) {
            $variants = $row['variants'] ?? [];
            unset($row['variants']);
            $this->create($row, $variants);
        }
        return count($rows);
    }
}
