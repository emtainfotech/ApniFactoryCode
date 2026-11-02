<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jsonData = file_get_contents('scratch/scraped_products.json');
$products = json_decode($jsonData, true);

echo "Loaded " . count($products) . " scraped products.\n";

$headers = [
    'category_name',
    'brand_name',
    'product_name',
    'title',
    'description',
    'hsn_code',
    'tax_percent',
    'box_packing',
    'shade_or_color',
    'shade_hexcode',
    'seller_price',
    'customer_mrp',
    'image_url_or_filename'
];

$allRows = [];
$uniqueShades = [];
$uniqueSizes = [];
$uniqueBrands = [];
$uniqueCategories = [];

foreach ($products as $p) {
    $cat = $p['category'];
    $brand = $p['brand'];
    $name = $p['name'];
    $title = $p['title'];
    $desc = $p['description'];
    $hsn = $p['hsn'];
    $tax = $p['tax'];
    $img = $p['image'];
    $sizes = $p['sizes'];
    $shades = $p['shades'];

    $uniqueCategories[$cat] = true;
    $uniqueBrands[$brand] = $cat;

    foreach ($shades as $sh) {
        $uniqueShades[$sh['name']] = $sh['hex'];
    }
    foreach ($sizes as $sz) {
        $uniqueSizes[$sz['name']] = true;
    }

    // Generate balanced rows:
    // If multiple sizes and multiple shades:
    // Include all sizes for the primary shade, and then include remaining shades with the most common size (e.g. 1 Ltr / 4 Ltr)
    $primaryShade = $shades[0] ?? ['name' => 'Standard', 'hex' => '#FFFFFF', 'image' => $img];
    $primarySize  = $sizes[0] ?? ['name' => '1 Litre', 'price' => 350.0, 'mrp' => 450.0];

    // 1. Add all sizes with the primary shade
    foreach ($sizes as $sIdx => $sz) {
        $variantImg = !empty($primaryShade['image']) ? $primaryShade['image'] : $img;
        $allRows[] = [
            $cat,
            $brand,
            $name,
            $title,
            $desc,
            $hsn,
            $tax,
            $sz['name'],
            $primaryShade['name'],
            $primaryShade['hex'],
            number_format($sz['price'], 2, '.', ''),
            number_format($sz['mrp'], 2, '.', ''),
            $variantImg
        ];
    }

    // 2. Add other shades (up to 8 representative shades to avoid giant 500-line rows for a single product)
    $otherShades = array_slice($shades, 1, 8);
    // Use the 1 Ltr or 4 Ltr size price for shade variations
    $repSize = $sizes[0];
    foreach ($sizes as $sz) {
        if (stripos($sz['name'], '1') !== false || stripos($sz['name'], '4') !== false) {
            $repSize = $sz;
            break;
        }
    }

    foreach ($otherShades as $osh) {
        $variantImg = !empty($osh['image']) ? $osh['image'] : $img;
        $allRows[] = [
            $cat,
            $brand,
            $name,
            $title,
            $desc,
            $hsn,
            $tax,
            $repSize['name'],
            $osh['name'],
            $osh['hex'],
            number_format($repSize['price'], 2, '.', ''),
            number_format($repSize['mrp'], 2, '.', ''),
            $variantImg
        ];
    }
}

echo "Total Generated Template Rows: " . count($allRows) . " across " . count($products) . " distinct products!\n";

// Helper function to convert index to column letter
function colLetter($idx) {
    $letter = '';
    $idx = $idx + 1;
    while ($idx > 0) {
        $mod = ($idx - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $idx = (int)(($idx - $mod) / 26);
    }
    return $letter;
}

function buildWorksheetXml($headers, $rows) {
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . "\n";
    $xml .= '  <sheetData>' . "\n";

    // Row 1: Header
    $xml .= '    <row r="1">' . "\n";
    foreach ($headers as $cIdx => $hVal) {
        $cRef = colLetter($cIdx) . '1';
        $esc = htmlspecialchars((string)$hVal, ENT_XML1, 'UTF-8');
        $xml .= "      <c r=\"{$cRef}\" t=\"inlineStr\"><is><t>{$esc}</t></is></c>\n";
    }
    $xml .= '    </row>' . "\n";

    // Rows 2..N: Data
    $rNum = 2;
    foreach ($rows as $r) {
        $xml .= "    <row r=\"{$rNum}\">\n";
        foreach ($r as $cIdx => $val) {
            $cRef = colLetter($cIdx) . $rNum;
            $esc = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
            $xml .= "      <c r=\"{$cRef}\" t=\"inlineStr\"><is><t>{$esc}</t></is></c>\n";
        }
        $xml .= "    </row>\n";
        $rNum++;
    }

    $xml .= '  </sheetData>' . "\n";
    $xml .= '</worksheet>';
    return $xml;
}

// 1. Build Multi-Sheet Excel (.xlsx)
$tempXlsx = storage_path('app/temp_populated_template.xlsx');
$zip = new ZipArchive();
if ($zip->open($tempXlsx, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Failed to create zip\n");
}

$zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet4.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/worksheets/sheet5.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

$zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

$zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>
  <Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet4.xml"/>
  <Relationship Id="rId5" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet5.xml"/>
</Relationships>');

$zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Products_Template" sheetId="1" r:id="rId1"/>
    <sheet name="Available_Shades" sheetId="2" r:id="rId2"/>
    <sheet name="Available_Sizes" sheetId="3" r:id="rId3"/>
    <sheet name="Available_Brands" sheetId="4" r:id="rId4"/>
    <sheet name="Categories_Reference" sheetId="5" r:id="rId5"/>
  </sheets>
</workbook>');

// Sheet 1: Products
$zip->addFromString('xl/worksheets/sheet1.xml', buildWorksheetXml($headers, $allRows));

// Sheet 2: Shades
$shadeRows = [];
$sId = 1;
foreach ($uniqueShades as $sName => $sHex) {
    $shadeRows[] = [$sId++, $sName, $sHex, $sHex];
}
$zip->addFromString('xl/worksheets/sheet2.xml', buildWorksheetXml(['Shade ID', 'Shade Name', 'Hex Code', 'Preview'], $shadeRows));

// Sheet 3: Sizes
$sizeRows = [];
$zId = 1;
foreach ($uniqueSizes as $zName => $t) {
    $sizeRows[] = [$zId++, $zName, '1'];
}
$zip->addFromString('xl/worksheets/sheet3.xml', buildWorksheetXml(['Size ID', 'Packing Name', 'Pieces Count'], $sizeRows));

// Sheet 4: Brands
$brandRows = [];
foreach ($uniqueBrands as $bName => $bCat) {
    $brandRows[] = [$bName, $bCat, 'ApniFactory Prime Mills'];
}
$zip->addFromString('xl/worksheets/sheet4.xml', buildWorksheetXml(['Brand Name', 'Category Name', 'Company'], $brandRows));

// Sheet 5: Categories
$catRows = [];
$cId = 1;
foreach ($uniqueCategories as $cName => $t) {
    $catRows[] = [$cId++, $cName, 'Paints & Coatings', 'Active'];
}
$zip->addFromString('xl/worksheets/sheet5.xml', buildWorksheetXml(['Category ID', 'Category Name', 'Main Category', 'Status'], $catRows));

$zip->close();
echo "XLSX file created: " . filesize($tempXlsx) . " bytes\n";

// 2. Build CSV file
$tempCsv = storage_path('app/temp_populated_template.csv');
$fp = fopen($tempCsv, 'w');
fputs($fp, "\xEF\xBB\xBF"); // UTF-8 BOM
fputcsv($fp, $headers);
foreach ($allRows as $r) {
    fputcsv($fp, $r);
}
fclose($fp);
echo "CSV file created: " . filesize($tempCsv) . " bytes\n";

// Copy to destinations
$destinations = [
    'c:/Users/EPC001/Desktop/ApniFactoryBackend/apnifactory_products_bulk_template.xlsx' => $tempXlsx,
    'c:/Users/EPC001/Desktop/apni_factory/apni_factory/apnifactory_products_bulk_template.xlsx' => $tempXlsx,
    'C:/Users/EPC001/.gemini/antigravity/brain/c7411eb4-6f80-4611-985e-d57fbe51aac3/apnifactory_products_bulk_template.xlsx' => $tempXlsx,

    'c:/Users/EPC001/Desktop/ApniFactoryBackend/apnifactory_products_bulk_sample.csv' => $tempCsv,
    'c:/Users/EPC001/Desktop/apni_factory/apni_factory/apnifactory_products_bulk_sample.csv' => $tempCsv,
    'C:/Users/EPC001/.gemini/antigravity/brain/c7411eb4-6f80-4611-985e-d57fbe51aac3/apnifactory_products_bulk_sample.csv' => $tempCsv
];

foreach ($destinations as $dest => $src) {
    copy($src, $dest);
    echo "Copied to: $dest (" . filesize($dest) . " bytes)\n";
}

@unlink($tempXlsx);
@unlink($tempCsv);

echo "=== SPREADSHEET FILES FULLY POPULATED SUCCESSFULLY ===\n";
