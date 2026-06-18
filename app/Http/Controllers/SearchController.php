<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global quick-search across all admin entities.
     * Returns grouped results for the header search dropdown.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['groups' => []]);
        }

        $like   = '%' . $q . '%';
        $groups = [];

        $products = Product::where('name', 'like', $like)
            ->with('category:id,name')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'name', 'category_id']);
        if ($products->isNotEmpty()) {
            $groups[] = [
                'type'  => 'product',
                'label' => 'Товары',
                'items' => $products->map(fn (Product $p) => [
                    'id'       => $p->id,
                    'title'    => $p->name,
                    'subtitle' => $p->category?->name,
                    'url'      => route('products.index', ['search' => $p->name]),
                ])->all(),
            ];
        }

        $categories = Category::where('name', 'like', $like)
            ->orderBy('order')
            ->limit(6)
            ->get(['id', 'name']);
        if ($categories->isNotEmpty()) {
            $groups[] = [
                'type'  => 'category',
                'label' => 'Категории',
                'items' => $categories->map(fn (Category $c) => [
                    'id'       => $c->id,
                    'title'    => $c->name,
                    'subtitle' => null,
                    'url'      => route('categories.index', ['search' => $c->name]),
                ])->all(),
            ];
        }

        $slides = Slide::where('title', 'like', $like)
            ->orderBy('order')
            ->limit(6)
            ->get(['id', 'title']);
        if ($slides->isNotEmpty()) {
            $groups[] = [
                'type'  => 'slide',
                'label' => 'Слайды',
                'items' => $slides->map(fn (Slide $s) => [
                    'id'       => $s->id,
                    'title'    => $s->title,
                    'subtitle' => null,
                    'url'      => route('slides.index'),
                ])->all(),
            ];
        }

        $staff = Staff::where(fn ($w) => $w
            ->where('name', 'like', $like)
            ->orWhere('position', 'like', $like)
            ->orWhere('department', 'like', $like))
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'position']);
        if ($staff->isNotEmpty()) {
            $groups[] = [
                'type'  => 'staff',
                'label' => 'Сотрудники',
                'items' => $staff->map(fn (Staff $s) => [
                    'id'       => $s->id,
                    'title'    => $s->name,
                    'subtitle' => $s->position,
                    'url'      => route('staff.index', ['search' => $s->name]),
                ])->all(),
            ];
        }

        $users = AdminUser::where(fn ($w) => $w
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like))
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'email']);
        if ($users->isNotEmpty()) {
            $groups[] = [
                'type'  => 'user',
                'label' => 'Администраторы',
                'items' => $users->map(fn (AdminUser $u) => [
                    'id'       => $u->id,
                    'title'    => $u->name,
                    'subtitle' => $u->email,
                    'url'      => route('users.index', ['search' => $u->name]),
                ])->all(),
            ];
        }

        return response()->json(['groups' => $groups]);
    }
}
