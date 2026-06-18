<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryRepository
{
    public function paginate(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        return Category::when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): void
    {
        $category->update($data);
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
