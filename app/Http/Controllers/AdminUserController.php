<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\AdminUser;
use App\Repositories\UserRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(private UserRepository $repo) {}

    public function index(): Response
    {
        $search = request('search', '');
        return Inertia::render('Users/Index', [
            'users'   => $this->repo->paginate($search),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());
        return back()->with('success', 'Пользователь добавлен.');
    }

    public function update(UpdateAdminUserRequest $request, AdminUser $user): RedirectResponse
    {
        $this->repo->update($user, $request->validated());
        return back()->with('success', 'Пользователь обновлён.');
    }

    public function destroy(AdminUser $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Нельзя удалить себя.');
        }
        $this->repo->delete($user);
        return back()->with('success', 'Пользователь удалён.');
    }
}
