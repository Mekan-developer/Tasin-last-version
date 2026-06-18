<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneProductImages extends Command
{
    protected $signature = 'products:prune-images
                            {--dry-run : Show orphaned files without deleting them}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Delete product image files on the public disk that are no longer referenced by any product';

    public function handle(): int
    {
        $disk = Storage::disk('public');

        // Every file currently stored under products/.
        $files = $disk->files('products');

        if (empty($files)) {
            $this->info('No files found in products/ — nothing to prune.');
            return self::SUCCESS;
        }

        // Build a set of image paths still referenced by a product.
        $referenced = [];
        foreach (Product::select('id', 'images')->cursor() as $product) {
            foreach ($product->images ?? [] as $image) {
                $referenced[$this->normalize($image)] = true;
            }
        }

        // Files on disk that no product points to are orphans.
        $orphans = array_values(array_filter(
            $files,
            fn (string $file) => ! isset($referenced[$this->normalize($file)])
        ));

        $this->info(sprintf(
            'Files on disk: %d · referenced: %d · orphaned: %d',
            count($files),
            count($referenced),
            count($orphans)
        ));

        if (empty($orphans)) {
            $this->info('No orphaned images. Disk is clean.');
            return self::SUCCESS;
        }

        $bytes = 0;
        foreach ($orphans as $file) {
            $bytes += $disk->size($file);
        }
        $this->line('Orphaned size: ' . $this->humanBytes($bytes));

        if ($this->option('dry-run')) {
            foreach ($orphans as $file) {
                $this->line('  • ' . $file);
            }
            $this->warn('Dry run — nothing deleted. Re-run without --dry-run to delete.');
            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm(sprintf('Delete %d orphaned image(s)?', count($orphans)), false)) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        $disk->delete($orphans);

        $this->info(sprintf(
            'Deleted %d orphaned image(s), freed %s.',
            count($orphans),
            $this->humanBytes($bytes)
        ));

        return self::SUCCESS;
    }

    /** Normalize a stored/disk path for comparison (forward slashes, no leading slash). */
    private function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $value = $bytes;
        $i = 0;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }
        return round($value, 2) . ' ' . $units[$i];
    }
}
