<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=apnifactory_local", "root", "");
foreach (["colors", "box_packings", "shade_cards", "categories", "brands", "companies"] as $t) {
    echo "=== $t ===\n";
    try {
        $stmt = $pdo->query("DESCRIBE $t");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $r["Field"] . " (" . $r["Type"] . ") ";
        }
        echo "\n";
        $cnt = $pdo->query("SELECT count(*) FROM $t")->fetchColumn();
        echo "Count: $cnt\n";
        $rows = $pdo->query("SELECT * FROM $t LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        print_r($rows);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n";
    }
}
