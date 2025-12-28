<?php
require __DIR__ . '/config/database.php';

$id = $_POST['id'] ?? null;
if($id){
$pdo->prepare("UPDATE musicas SET tocada=1 WHERE id=?")
->execute([$id]);
}
