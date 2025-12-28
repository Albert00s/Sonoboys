<?php
require_once __DIR__ . "/config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Acesso inválido.");
}

$grupo = $_POST["grupo"] ?? null;
$visitante = $_POST["nome_visitante"] ?? null;
$songs = $_POST["songs"] ?? [];

if (!$grupo || empty($songs)) {
    die("Dados incompletos.");
}

try {
    $pdo->beginTransaction();

    // SALVAR ENVIO
    $stmt = $pdo->prepare("
        INSERT INTO envios (grupo, nome_visitante)
        VALUES (:grupo, :nome_visitante)
    ");

    $stmt->execute([
        ":grupo" => $grupo,
        ":nome_visitante" => $grupo === "visitante" ? $visitante : null
    ]);

    $envioId = $pdo->lastInsertId();

    // PREPARAR UPLOAD
    $uploadDir = __DIR__ . "/uploads/musicas/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ordem = 1;

    foreach ($songs as $index => $song) {

        $titulo = trim($song["titulo"] ?? "");
        $tipo = $song["tipo"] ?? null;
        $playback = ($song["playback"] ?? "nao") === "sim" ? 1 : 0;
        $observacao = trim($song["observacao"] ?? "");

        if (!$titulo || !$tipo) continue;

        $arquivo = null;
        $link = null;

        if ($tipo === "arquivo" && isset($_FILES["songs"]["name"][$index]["arquivo"])) {

            $tmp  = $_FILES["songs"]["tmp_name"][$index]["arquivo"];
            $nome = $_FILES["songs"]["name"][$index]["arquivo"];
            $erro = $_FILES["songs"]["error"][$index]["arquivo"];

            if ($erro === UPLOAD_ERR_OK) {

                $ext = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
                if (!in_array($ext, ["mp3", "wav"])) {
                    throw new Exception("Formato inválido.");
                }

                $novoNome = uniqid("musica_") . "." . $ext;
                move_uploaded_file($tmp, $uploadDir . $novoNome);

                $arquivo = "uploads/musicas/" . $novoNome;
            }
        }

        if ($tipo === "link") {
            $link = trim($song["link"] ?? "");
        }

        $stmt = $pdo->prepare("
            INSERT INTO musicas
            (envio_id, titulo, tipo, arquivo, link, playback, observacao, ordem)
            VALUES
            (:envio_id, :titulo, :tipo, :arquivo, :link, :playback, :observacao, :ordem)
        ");

        $stmt->execute([
            ":envio_id" => $envioId,
            ":titulo" => $titulo,
            ":tipo" => $tipo,
            ":arquivo" => $arquivo,
            ":link" => $link,
            ":playback" => $playback,
            ":observacao" => $observacao,
            ":ordem" => $ordem++
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Erro ao salvar as músicas.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Envio concluído</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --azul:#1565c0;
    --azul-claro:#e3f2fd;
    --branco:#ffffff;
    --cinza:#f5f7fa;
    --texto:#1f2937;
}

/* RESET */
*{box-sizing:border-box;font-family:"Segoe UI",Arial}

/* BODY */
body{
    margin:0;
    background:var(--cinza);
}

/* PRELOADER */
#preloader{
    position:fixed;
    inset:0;
    background:var(--branco);
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    z-index:9999;
}
.loader{
    width:60px;
    height:60px;
    border:6px solid var(--azul-claro);
    border-top:6px solid var(--azul);
    border-radius:50%;
    animation:spin 1s linear infinite;
}
#preloader span{
    margin-top:15px;
    font-weight:600;
    color:var(--azul);
}
@keyframes spin{to{transform:rotate(360deg)}}

/* MODAL */
.wrapper{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
}
.modal{
    background:var(--branco);
    padding:35px;
    border-radius:14px;
    width:90%;
    max-width:420px;
    text-align:center;
    box-shadow:0 25px 50px rgba(0,0,0,.12);
    animation:fadeUp .5s ease;
}
@keyframes fadeUp{
    from{opacity:0;transform:translateY(30px)}
    to{opacity:1}
}
.modal h2{
    color:var(--azul);
    margin-bottom:10px;
}
.modal p{
    color:#374151;
    margin-bottom:25px;
}
.modal button{
    border:none;
    padding:12px 28px;
    border-radius:8px;
    background:var(--azul);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}
.modal button:hover{
    opacity:.9;
    transform:scale(1.05);
}
</style>
</head>

<body>

<div id="preloader">
    <div class="loader"></div>
    <span>Finalizando envio...</span>
</div>

<div class="wrapper" style="display:none" id="content">
    <div class="modal">
        <h2>Envio realizado com sucesso</h2>
        <p>As músicas foram registradas corretamente no sistema.</p>
        <button onclick="window.location.href='index.html'">OK</button>
    </div>
</div>

<script>
window.addEventListener("load",()=>{
    document.getElementById("preloader").style.display="none";
    document.getElementById("content").style.display="flex";
});
</script>

</body>
</html>
