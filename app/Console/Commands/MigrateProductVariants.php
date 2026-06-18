<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateProductVariants extends Command
{
    protected $signature = 'products:migrate-variants
                            {--force : Skip confirmation prompt}';

    protected $description = 'Migrate product variants from products.variants JSON column to product_variants table';

    public function handle(): int
    {
        if (! Schema::hasColumn('products', 'variants')) {
            $this->error('Column products.variants does not exist — migration may have already run.');
            return self::FAILURE;
        }

        $products = DB::table('products')
            ->whereNotNull('variants')
            ->where('variants', '!=', 'null')
            ->get(['id', 'variants']);

        $total = $products->count();

        if ($total === 0) {
            $this->info('No products with variants data found.');
            return self::SUCCESS;
        }

        $this->info("Found {$total} products with variants.");

        if (! $this->option('force') && ! $this->confirm('Migrate to product_variants table?', true)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        $migrated = 0;
        $skipped  = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $product) {
            if (DB::table('product_variants')->where('product_id', $product->id)->exists()) {
                $skipped++;
                $bar->advance();
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
                $migrated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Migrated: {$migrated} products.");

        if ($skipped) {
            $this->warn("Skipped: {$skipped} (already had rows in product_variants).");
        }

        $this->line('Run <info>php artisan migrate</info> to drop the variants column.');

        return self::SUCCESS;
    }
}
