<?php

namespace App\Repositories;

use App\Models\AdminUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
    public function paginate(string $search = '', int $perPage = 20): LengthAwarePaginator
    {
        return AdminUser::when($search, fn ($q) =>
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
        )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): AdminUser
    {
        $data['password'] = Hash::make($data['password']);
        unset($data['password_confirmation']);
        return AdminUser::create($data);
    }

    public function update(AdminUser $user, array $data): void
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        unset($data['password_confirmation']);
        $user->update($data);
    }

    public function delete(AdminUser $user): void
    {
        $user->delete();
    }
}
