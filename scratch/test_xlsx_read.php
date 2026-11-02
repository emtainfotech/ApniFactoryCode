<?php
$zip = new ZipArchive();
$path = __DIR__ . "/sample_test.xlsx";
if ($zip->open($path) === true) {
    echo "Opened zip successfully! Files in zip:\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        echo " - " . $zip->getNameIndex($i) . "\n";
    }
    $sheetXml = $zip->getFromName("xl/worksheets/sheet1.xml");
    $xml = simplexml_load_string($sheetXml);
    foreach ($xml->sheetData->row as $row) {
        $rNum = (string)$row['r'];
        $cols = [];
        foreach ($row->c as $cell) {
            $val = '';
            if (isset($cell->is->t)) {
                $val = (string)$cell->is->t;
            } elseif (isset($cell->v)) {
                $val = (string)$cell->v;
            }
            $cols[] = (string)$cell['r'] . ": " . $val;
        }
        echo "Row $rNum: " . implode(" | ", $cols) . "\n";
    }
    $zip->close();
} else {
    echo "Could not open zip\n";
}

