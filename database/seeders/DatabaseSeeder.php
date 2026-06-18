<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::factory()->create([
            'name' => 'Mekan Agamyradov',
            'email' => 'admin@gmail.com',
        ]);
    }
}
