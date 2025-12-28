<?php
require __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acesso inválido');
}

$id = $_POST['id'] ?? null;

if (!$id) {
    die('ID inválido');
}

/*
|--------------------------------------------------------------------------
| Busca o arquivo da música (se existir)
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT arquivo FROM musicas WHERE id = ?");
$stmt->execute([$id]);
$musica = $stmt->fetch();

if (!$musica) {
    die('Música não encontrada');
}

/*
|--------------------------------------------------------------------------
| Remove o arquivo físico
|--------------------------------------------------------------------------
*/
if (!empty($musica['arquivo'])) {
    $caminho = __DIR__ . '/' . $musica['arquivo'];
    if (file_exists($caminho)) {
        unlink($caminho);
    }
}

/*
|--------------------------------------------------------------------------
| Remove do banco
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("DELETE FROM musicas WHERE id = ?");
$stmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Redireciona de volta ao painel
|--------------------------------------------------------------------------
*/
header("Location: admin/admin.php");
exit;
