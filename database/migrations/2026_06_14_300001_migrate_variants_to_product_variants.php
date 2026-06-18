<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $products = DB::table('products')
            ->whereNotNull('variants')
            ->where('variants', '!=', 'null')
            ->get(['id', 'variants']);

        foreach ($products as $product) {
            if (DB::table('product_variants')->where('product_id', $product->id)->exists()) {
                continue;
            }

            $variants = json_decode($product->variants, true) ?? [];
            $rows = [];

            foreach ($variants as $v) {
                $name = $v['model'] ?? '';
                if ($name === '') continue;

                $rawPrice = $v['price'] ?? '';
                $price = ($rawPrice !== '' && is_numeric($rawPrice)) ? (float) $rawPrice : 0.00;

                $rows[] = [
                    'product_id' => $product->id,
                    'name'       => $name,
                    'price'      => $price,
                    'currency'   => 'USD',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($rows) {
                DB::table('product_variants')->insert($rows);
            }
        }

        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('variants'));
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('variants')->nullable()->after('is_new');
        });

        $grouped = DB::table('product_variants')
            ->get(['product_id', 'name', 'price'])
            ->groupBy('product_id');

        foreach ($grouped as $productId => $variants) {
            $json = $variants->map(fn ($v) => [
                'model' => $v->name,
                'price' => (string) $v->price,
            ])->values()->toArray();

            DB::table('products')
                ->where('id', $productId)
                ->update(['variants' => json_encode($json)]);
        }
    }
};
