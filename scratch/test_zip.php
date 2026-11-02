<?php
$zip = new ZipArchive();
$filename = "scratch/test_excel.xlsx";
if ($zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    echo "ZipArchive works perfectly!\n";
    $zip->close();
    unlink($filename);
} else {
    echo "Failed to create zip\n";
}

