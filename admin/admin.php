<?php
require __DIR__ . '/../config/database.php';

$sql = "
    SELECT 
        m.id,
        m.titulo,
        m.arquivo,
        m.link,
        m.playback,
        m.tocada,
        m.ordem,
        e.grupo,
        e.nome_visitante AS visitante,
        m.observacao
    FROM musicas m
    JOIN envios e ON e.id = m.envio_id
    ORDER BY m.ordem ASC, m.id ASC
";

$musicas = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Painel do Sonoplasta</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{
    --azul:#1e88e5;
    --azul-escuro:#0d47a1;
    --azul-claro:#e3f2fd;
    --cinza:#f5f7fa;
    --cinza-escuro:#e0e6ed;
    --branco:#ffffff;
    --texto:#1f2937;
    --sucesso:#2e7d32;
    --erro:#c62828;
}

/* RESET */
*{
    box-sizing:border-box;
    font-family:"Segoe UI",Arial,sans-serif;
}

/* BODY */
body{
    margin:0;
    padding:30px;
    min-height:100vh;
    background:linear-gradient(135deg,var(--azul),var(--azul-escuro));
}

/* TÍTULO */
h1{
    text-align:center;
    color:#fff;
    margin-bottom:30px;
    font-weight:700;
    letter-spacing:.4px;
}

/* PAINEL */
.panel{
    max-width:1100px;
    margin:auto;
    background:var(--branco);
    padding:30px;
    border-radius:18px;
    box-shadow:0 30px 60px rgba(0,0,0,.25);
    animation:fadeUp .5s ease;
}
@keyframes fadeUp{
    from{opacity:0;transform:translateY(25px)}
    to{opacity:1}
}

/* PLAYER */
.player{
    background:var(--azul-claro);
    padding:18px 20px;
    border-radius:14px;
    margin-bottom:28px;
    border:1px solid var(--cinza-escuro);
}
.player strong{
    display:block;
    margin-bottom:8px;
    color:var(--azul-escuro);
    font-weight:600;
}
audio{
    width:100%;
}

/* LISTA */
#lista{
    display:flex;
    flex-direction:column;
    gap:18px;
}

/* CARD MÚSICA */
.song{
    background:var(--cinza);
    border-left:6px solid var(--azul);
    padding:20px 22px;
    border-radius:16px;
    cursor:grab;
    transition:.25s ease;
}
.song:hover{
    background:#fff;
    transform:translateY(-2px);
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

/* TOCADA */
.song.tocada{
    opacity:.55;
    background:var(--cinza-escuro);
    border-left-color:var(--azul-escuro);
    text-decoration:line-through;
}

/* HEADER */
.song-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:8px;
}
.song-title{
    font-size:17px;
    font-weight:700;
    color:var(--azul-escuro);
}

/* BADGE */
.badge{
    padding:4px 14px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    background:var(--azul-claro);
    color:var(--azul-escuro);
    border:1px solid var(--azul);
}

/* INFO */
.song p{
    margin:8px 0 14px;
    font-size:14px;
    color:var(--texto);
    line-height:1.5;
}

/* AÇÕES */
.song-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

/* BOTÕES */
button{
    border:none;
    padding:9px 16px;
    border-radius:10px;
    font-weight:700;
    cursor:pointer;
    transition:.25s ease;
}

/* PLAY */
.btn-play{
    background:var(--azul);
    color:#fff;
}
.btn-play:hover{
    background:var(--azul-escuro);
}

/* TOCADA */
.btn-done{
    background:var(--azul-claro);
    color:var(--azul-escuro);
    border:1px solid var(--azul);
}
.btn-done:hover{
    background:var(--azul);
    color:#fff;
}

/* REMOVER */
.btn-remove{
    background:#fff;
    color:var(--erro);
    border:1px solid var(--erro);
}
.btn-remove:hover{
    background:var(--erro);
    color:#fff;
}

/* DRAG */
.song:active{
    cursor:grabbing;
    opacity:.75;
}
</style>
</head>

<body>

<h1>Painel do Sonoplasta</h1>

<div class="panel">

<div class="player">
<strong id="nowPlaying">Nenhuma música tocando</strong>
<audio id="audioPlayer" controls></audio>
</div>

<div id="lista">
<?php foreach($musicas as $m): ?>
<div class="song <?= $m['tocada'] ? 'tocada' : '' ?>"
draggable="true"
data-id="<?= $m['id'] ?>">

<div class="song-header">
<span class="song-title"><?= htmlspecialchars($m['titulo']) ?></span>
<span class="badge <?= $m['playback'] ? 'playback' : '' ?>">
<?= $m['playback'] ? 'Playback' : 'Ao vivo' ?>
</span>
</div>

<p>
<strong>Quem canta:</strong>
<?= htmlspecialchars($m['grupo']) ?>
<?= $m['grupo']=='visitante' ? ' - '.htmlspecialchars($m['visitante']) : '' ?>
<br>
<strong>Obs:</strong> <?= htmlspecialchars($m['observacao'] ?: 'Nenhuma') ?>
</p>

<div class="song-actions">

<?php if($m['arquivo']): ?>
<button class="btn-play"
onclick="playSong('../<?= $m['arquivo'] ?>','<?= htmlspecialchars($m['titulo']) ?>',<?= $m['id'] ?>)">
▶ Tocar
</button>
<?php else: ?>
<button onclick="window.open('<?= $m['link'] ?>','_blank')">🔗 Link</button>
<?php endif; ?>

<button class="btn-done" onclick="marcarTocada(<?= $m['id'] ?>)">
✔ Tocada
</button>

<form action="../remover.php" method="POST"
onsubmit="return confirm('Remover esta música?')">
<input type="hidden" name="id" value="<?= $m['id'] ?>">
<button class="btn-remove">🗑 Remover</button>
</form>

</div>
</div>
<?php endforeach; ?>
</div>

</div>

<script>
const player=document.getElementById('audioPlayer');
const now=document.getElementById('nowPlaying');

function playSong(src,titulo,id){
now.textContent='Tocando: '+titulo;
player.src=src;
player.play();
fetch('../marcar_tocada.php',{method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id});
}

/* MARCAR TOCADA */
function marcarTocada(id){
fetch('../marcar_tocada.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'id='+id
}).then(()=>location.reload());
}

/* DRAG & DROP */
let drag;
document.querySelectorAll('.song').forEach(el=>{
el.addEventListener('dragstart',()=>drag=el);
el.addEventListener('dragover',e=>e.preventDefault());
el.addEventListener('drop',()=>{
if(drag!==el){
el.before(drag);
salvarOrdem();
}
});
});

function salvarOrdem(){
const ids=[...document.querySelectorAll('.song')]
.map((el,i)=>`ids[]=${el.dataset.id}&ordem[]=${i+1}`).join('&');

fetch('../salvar_ordem.php',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:ids
});
}
</script>

</body>
</html>
