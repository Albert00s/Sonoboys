<?php
require __DIR__ . '/config/database.php';

$ids = $_POST['ids'] ?? [];
$ordens = $_POST['ordem'] ?? [];

for($i=0;$i<count($ids);$i++){
$pdo->prepare("UPDATE musicas SET ordem=? WHERE id=?")
->execute([$ordens[$i],$ids[$i]]);
}
