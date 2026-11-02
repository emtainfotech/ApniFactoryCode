<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

$jsonProducts = json_decode(file_get_contents(__DIR__ . '/scraped_products.json'), true);

$destDir1 = public_path('storage/product');
$destDir2 = storage_path('app/public/product');
@mkdir($destDir1, 0755, true);
@mkdir($destDir2, 0755, true);

$ctx = stream_context_create([
    'http' => [
        'timeout' => 12,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false]
]);

echo "Checking & downloading product images for " . count($jsonProducts) . " products...\n";

foreach ($jsonProducts as $pData) {
    $pName = trim($pData['name']);
    $remoteImg = trim($pData['image'] ?? '');
    
    // Also check first shade image if primary image is empty
    if (empty($remoteImg) && !empty($pData['shades'][0]['image'])) {
        $remoteImg = trim($pData['shades'][0]['image']);
    }

    $product = Product::where('name', $pName)->first();
    if (!$product) {
        echo "Product not found: {$pName}\n";
        continue;
    }

    $safeBase = 'bulk_' . strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $pName));
    
    if (!empty($remoteImg)) {
        // Encode spaces and special chars in URL path
        $cleanUrl = str_replace(' ', '%20', $remoteImg);
        
        $pathPart = parse_url($cleanUrl, PHP_URL_PATH);
        $ext = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION) ?: 'png');
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'jfif'])) {
            $ext = 'png';
        }
        
        $filename = "{$safeBase}.{$ext}";
        $savePath1 = "{$destDir1}/{$filename}";
        $savePath2 = "{$destDir2}/{$filename}";
        
        $downloaded = false;
        if (file_exists($savePath1) && filesize($savePath1) > 500) {
            $downloaded = true;
        } else {
            echo "Fetching: {$cleanUrl} -> {$filename} ...\n";
            $bytes = @file_get_contents($cleanUrl, false, $ctx);
            if (!empty($bytes) && strlen($bytes) > 500) {
                file_put_contents($savePath1, $bytes);
                file_put_contents($savePath2, $bytes);
                $downloaded = true;
                echo "   -> Saved " . strlen($bytes) . " bytes\n";
            } else {
                echo "   -> Failed or empty from remote!\n";
            }
        }
        
        if ($downloaded) {
            $product->image = "product/{$filename}";
            $product->multipleimages = json_encode(["product/{$filename}"]);
            $product->save();
            echo "Updated product ID {$product->id} [{$pName}] with image: product/{$filename}\n";
            continue;
        }
    }

    // Fallback: If no remote image or download failed, generate a crisp product card image using SVG converted or clean paint bucket asset
    $catSlug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $product->category->name ?? 'paint'));
    $catImg1 = public_path("storage/category/{$catSlug}.png");
    if (file_exists($catImg1)) {
        $filename = "{$safeBase}.png";
        copy($catImg1, "{$destDir1}/{$filename}");
        copy($catImg1, "{$destDir2}/{$filename}");
        $product->image = "product/{$filename}";
        $product->multipleimages = json_encode(["product/{$filename}"]);
        $product->save();
        echo "Used category asset for product ID {$product->id} [{$pName}]\n";
    }
}

// Also ensure all brands have an attractive logo copied if default
$brands = Brand::all();
$defaultImg = public_path('img/Apni-Factory-3.png');
foreach ($brands as $b) {
    if (empty($b->image) || strpos($b->image, 'default') !== false || !file_exists(public_path('storage/' . $b->image))) {
        $bSlug = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $b->name));
        $brandFile = "brand/{$bSlug}_logo.png";
        $p1 = public_path("storage/{$brandFile}");
        $p2 = storage_path("app/public/{$brandFile}");
        @mkdir(dirname($p1), 0755, true);
        @mkdir(dirname($p2), 0755, true);
        
        // Find existing brand image or copy default
        $found = false;
        foreach (glob(public_path('storage/brand/*.*')) as $exFile) {
            $fn = strtolower(basename($exFile));
            if (strpos($fn, strtolower(explode(' ', $b->name)[0])) !== false) {
                copy($exFile, $p1);
                copy($exFile, $p2);
                $found = true;
                break;
            }
        }
        if (!$found && file_exists($defaultImg)) {
            copy($defaultImg, $p1);
            copy($defaultImg, $p2);
        }
        $b->image = $brandFile;
        $b->save();
        echo "Updated Brand ID {$b->id} [{$b->name}] with logo: {$brandFile}\n";
    }
}

echo "DONE!\n";
