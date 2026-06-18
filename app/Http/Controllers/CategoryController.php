<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Repositories\CategoryRepository;
use App\Traits\ConvertsToWebp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use ConvertsToWebp;
    public function __construct(private CategoryRepository $repo) {}

    public function index(): Response
    {
        $search = request('search', '');
        return Inertia::render('Categories/Index', [
            'categories' => $this->repo->paginate($search),
            'filters'    => ['search' => $search],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('icon');
        if ($request->hasFile('icon')) {
            $data['image_icon'] = $this->storeAsWebp($request->file('icon'), 'categories');
        }
        $this->repo->create($data);
        return back()->with('success', 'Категория добавлена.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except('icon');
        if ($request->hasFile('icon')) {
            if ($category->image_icon) {
                Storage::disk('public')->delete($category->image_icon);
            }
            $data['image_icon'] = $this->storeAsWebp($request->file('icon'), 'categories');
        }
        $this->repo->update($category, $data);
        return back()->with('success', 'Категория обновлена.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->image_icon) {
            Storage::disk('public')->delete($category->image_icon);
        }
        $this->repo->delete($category);
        return back()->with('success', 'Категория удалена.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $field = request('field', 'is_active');
        abort_unless(in_array($field, ['is_active', 'show_price']), 422);
        $category->update([$field => !$category->$field]);
        return back();
    }
}
