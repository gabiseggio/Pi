<?php
session_start();
require_once("../config/connection.php");

// Se não estiver logado, manda pro login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Pega usuário logado
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$tipo = $_SESSION['tipo'];
$foto_perfil = $_SESSION['foto_perfil'] ?? 'default.png';

// Busca todos os animais disponíveis
$sql = "SELECT a.*, u.username, u.foto_perfil 
        FROM animais a 
        JOIN usuarios u ON a.id_usuario = u.id
        WHERE a.status = 'disponivel'
        ORDER BY a.data_postagem DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagem de notificações
$notif_count = 0;
if($tipo === 'doador'){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM formularios_adocao f JOIN animais a ON f.id_animal = a.id WHERE a.id_usuario=:id AND f.status='aguardando'");
    $stmt->execute([':id'=>$user_id]);
    $notif_count = $stmt->fetchColumn();
} else if($tipo === 'adotante'){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM formularios_adocao WHERE id_adotante=:id AND status!='aguardando'");
    $stmt->execute([':id'=>$user_id]);
    $notif_count = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Home - Adoções</title>
  <style>
    body { margin:0; font-family:Arial,sans-serif; background:#fff; display:flex; }
    .sidebar { width:200px; background:#f0f0f0; padding:20px; height:100vh; box-shadow:2px 0 5px rgba(0,0,0,0.1); }
    .sidebar h3 { margin-bottom:20px; display:flex; align-items:center; }
    .sidebar img { width:40px; height:40px; border-radius:50%; margin-right:10px; object-fit:cover; }
    .sidebar a { display:block; margin:12px 0; text-decoration:none; color:#333; font-weight:bold; }
    .content { flex:1; padding:20px; }
    .animal-card { border:1px solid #ddd; border-radius:12px; padding:15px; margin-bottom:20px; box-shadow:0 0 5px rgba(0,0,0,0.1); max-width:500px; }
    .animal-header { display:flex; align-items:center; margin-bottom:10px; }
    .animal-header img { width:35px; height:35px; border-radius:50%; object-fit:cover; margin-right:10px; }
    .animal-card h3 { margin:5px 0; font-size:1.2em; }
    .animal-card img.animal-foto { max-width:100%; max-height:200px; border-radius:10px; display:block; margin:10px auto; object-fit:cover; }
    .actions { margin-top:10px; display:flex; gap:15px; align-items:center; }
    .actions button { background:none; border:none; font-size:1.2em; cursor:pointer; color:#666; transition:color 0.2s; }
    .actions button:hover { color:#000; }
    .adopt { margin-left:auto; padding:6px 12px; border-radius:8px; background:#28a745; color:#fff; font-size:0.9em; font-weight:bold; cursor:pointer; border:none; }
    .comentario-form { margin-top:10px; display:none; }
    .comentario-form input { width:80%; padding:6px; border-radius:6px; border:1px solid #ccc; margin-right:5px; }
    .comentario-form button { padding:6px 10px; border-radius:6px; border:none; background:#007bff; color:#fff; cursor:pointer; }
    .notif-count { background:#dc3545; color:#fff; border-radius:50%; padding:2px 6px; font-size:0.8em; margin-left:5px; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h3>
      <img src="../uploads/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto perfil">
      @<?php echo htmlspecialchars($username); ?>
    </h3>
    <a href="home.php">🏠 Início</a>
    <a href="<?php echo $tipo==='doador' ? 'notificacoes.php' : 'notificacoes_adotante.php'; ?>">
      🔔 Notificações
      <?php if($notif_count>0): ?>
        <span class="notif-count"><?php echo $notif_count; ?></span>
      <?php endif; ?>
    </a>
    <a href="mensagens.php">💬 Mensagens</a>
    <a href="perfil.php">👤 Perfil</a>
    <?php if ($tipo === "doador"): ?>
      <a href="postar_animal.php">➕ Postar Animal</a>
    <?php endif; ?>
    <a href="../auth/logout.php">🚪 Sair</a>
  </div>

  <!-- Timeline -->
  <div class="content">
    <h2>Timeline</h2>
    <?php foreach ($animais as $animal): ?>
      <div class="animal-card">
        <div class="animal-header">
          <img src="../uploads/<?php echo htmlspecialchars($animal['foto_perfil'] ?? 'default.png'); ?>" alt="Perfil dono">
          <span>@<?php echo htmlspecialchars($animal['username']); ?></span>
        </div>

        <h3><?php echo htmlspecialchars($animal['nome']); ?> (<?php echo $animal['tipo']; ?>)</h3>
        <p><b>Idade:</b> <?php echo htmlspecialchars($animal['idade']); ?></p>
        <p><b>Raça:</b> <?php echo htmlspecialchars($animal['raca']); ?></p>
        <p><b>Sexo:</b> <?php echo htmlspecialchars($animal['sexo']); ?></p>
        <p><b>Localização:</b> <?php echo htmlspecialchars($animal['localizacao']); ?></p>
        <?php if ($animal['foto']): ?>
          <img class="animal-foto" src="../uploads/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto do animal">
        <?php endif; ?>
        <p><?php echo nl2br(htmlspecialchars($animal['observacoes'])); ?></p>

        <div class="actions">
          <button class="like" data-animal="<?php echo $animal['id']; ?>" title="Curtir">❤️</button>
          <button class="comment" data-animal="<?php echo $animal['id']; ?>" title="Comentar">💬</button>
          <?php if($_SESSION['tipo'] === 'adotante' && $animal['status'] === 'disponivel'): ?>
            <button onclick="window.location.href='solicitar_adocao.php?id_animal=<?php echo $animal['id']; ?>'" class="adopt">
              Quero Adotar
            </button>
          <?php endif; ?>
        </div>

        <div class="comentario-form">
          <input type="text" class="comentario-texto" placeholder="Escreva um comentário...">
          <button class="enviar-comentario">Enviar</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <script>
  // Curtir
  document.querySelectorAll('.like').forEach(btn => {
      btn.addEventListener('click', () => {
          const id = btn.getAttribute('data-animal');
          fetch('interacao.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/x-www-form-urlencoded'},
              body: `id_animal=${id}&tipo=curtida`
          })
          .then(res => res.json())
          .then(data => {
              if(data.acao === 'curtir') btn.style.color = 'red';
              else btn.style.color = '#666';
          });
      });
  });

  // Mostrar/Ocultar formulário de comentário
  document.querySelectorAll('.comment').forEach(btn => {
      btn.addEventListener('click', () => {
          const form = btn.closest('.animal-card').querySelector('.comentario-form');
          form.style.display = form.style.display === 'none' ? 'block' : 'none';
      });
  });

  // Enviar comentário
  document.querySelectorAll('.enviar-comentario').forEach(btn => {
      btn.addEventListener('click', () => {
          const card = btn.closest('.animal-card');
          const id = card.querySelector('.like').getAttribute('data-animal');
          const input = card.querySelector('.comentario-texto');
          const texto = input.value;
          if(!texto.trim()) return alert('Comentário vazio');
          fetch('interacao.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/x-www-form-urlencoded'},
              body: `id_animal=${id}&tipo=comentario&comentario_texto=${encodeURIComponent(texto)}`
          })
          .then(res => res.json())
          .then(data => {
              if(data.sucesso) {
                  alert('Comentário enviado!');
                  input.value = '';
                  card.querySelector('.comentario-form').style.display = 'none';
              }
          });
      });
  });
  </script>
</body>
</html>