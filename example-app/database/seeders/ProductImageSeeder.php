<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $base = public_path('images/products/Bundle deals');
        if (!is_dir($base)) {
            $this->command->warn("Images base not found: {$base}");
            return;
        }

        $files = collect($this->scan($base))
            ->filter(fn($p) => preg_match('/\.(png|jpe?g|webp)$/i', $p))
            ->reject(fn($p) => str_contains($p, 'Bundle Products'));

        $categoryCache = [];

        foreach ($files as $file) {
            $rel = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $file);
            $relative = str_replace($base . DIRECTORY_SEPARATOR, '', $file);
            $parts = collect(explode(DIRECTORY_SEPARATOR, $relative));
            // Expected like: Brand Category / filename
            $group = $parts->count() > 1 ? $parts[0] : 'General';
            $folder = $group; // e.g., "CeraVe serums", "LaRochePosay Cleansers"
            $title = pathinfo($file, PATHINFO_FILENAME);
            $title = str_replace(['_', '-'], ' ', $title);

            $categoryName = $this->normalizeCategory($folder);
            if (!isset($categoryCache[$categoryName])) {
                $category = Category::firstOrCreate(['CategoryName' => $categoryName]);
                $categoryCache[$categoryName] = $category->CategoryID;
            }
            $categoryId = $categoryCache[$categoryName];

            $price = $this->suggestPrice($title, $folder);
            $desc = $this->suggestDescription($title, $folder);

            Product::updateOrCreate(
                ['Title' => $title, 'CategoryID' => $categoryId],
                [
                    'Price' => $price,
                    'Description' => $desc,
                    'Image' => $rel,
                ]
            );
        }

        $this->command->info('ProductImageSeeder: seeded products from images.');
    }

    private function scan(string $dir): array
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $files[] = $file->getPathname();
        }
        return $files;
    }

    private function suggestPrice(string $title, string $folder): float
    {
        $t = strtolower($title . ' ' . $folder);
        if (str_contains($t, 'bundle')) return 39.99;
        if (str_contains($t, 'serum')) return 22.99;
        if (str_contains($t, 'cleanser')) return 14.49;
        if (str_contains($t, 'moistur')) return 18.99;
        if (str_contains($t, 'spf') || str_contains($t, 'sunscreen')) return 21.49;
        return 16.99;
    }

    private function suggestDescription(string $title, string $folder): string
    {
        $brand = $this->extractBrand($folder);
        return sprintf(
            '%s by %s — dermatologist-recommended formula designed for daily use. Gently improves skin comfort and barrier while delivering visible results.',
            trim($title),
            trim($brand ?: 'BeautyBreeze')
        );
    }

    private function normalizeCategory(string $group): string
    {
        // Convert variations to clean "Brand • Type" label so filters look nice
        $g = trim($group);
        $g = str_replace(['  '], ' ', $g);
        // Keep as-is but collapse underscores/dashes to spaces
        $g = str_replace(['_', '-'], [' ', '-'], $g);
        return $g;
    }

    private function extractBrand(string $group): string
    {
        // Brand is text before first space for simple cases, but support multi-word brands
        $lower = strtolower($group);
        foreach ([
            'la roche-posay' => 'La Roche-Posay',
            'larocheposay' => 'La Roche-Posay',
            'cerave' => 'CeraVe',
            'cetaphil' => 'Cetaphil',
            'ordinary' => 'The Ordinary',
        ] as $needle => $brand) {
            if (str_contains($lower, $needle)) return $brand;
        }
        return explode(' ', trim($group))[0] ?? '';
    }
}
