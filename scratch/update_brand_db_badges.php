<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Brand;

$map = [
    'Creation'                                  => 'brand/badge_creation.png',
    'Autocoats'                                 => 'brand/badge_autocoats.png',
    'Autocoats Enamel'                          => 'brand/badge_autocoats_enamel.png',
    'Shrirang'                                  => 'brand/badge_shrirang.png',
    'Bright'                                    => 'brand/badge_bright.png',
    'Bright Auto Paint'                         => 'brand/badge_bright_auto_paint.png',
    'Asian Paints Pro'                          => 'brand/badge_asian_paints_pro.png',
    'Berger ColorCraft'                         => 'brand/badge_berger_colorcraft.png',
    'UltraShield Extreme Armor'                 => 'brand/badge_ultrashield.png',
    'Parijat'                                   => 'brand/badge_parijat.png',
    'MasterCraft Tools'                         => 'brand/badge_mastercraft.png',
    'FactoryStandard'                           => 'brand/badge_factorystandard.png',
    'Creation Gold Luxury Silk Emulsion Base'   => 'brand/badge_creation_gold.png',
    'Raymond Apparel'                           => 'brand/badge_raymond.png',
    'Vardhman Textiles'                         => 'brand/badge_vardhman.png',
    'Mysore Silk Works'                         => 'brand/badge_mysoresilk.png',
    'FabIndia Crafts'                           => 'brand/badge_fabindia.png',
];

foreach ($map as $brandName => $imgRel) {
    $brands = Brand::where('name', $brandName)->get();
    foreach ($brands as $b) {
        $b->image = $imgRel;
        $b->save();
        echo "Updated Brand ID {$b->id} [{$b->name}] -> {$imgRel}\n";
    }
}

echo "All brand records updated successfully!\n";
