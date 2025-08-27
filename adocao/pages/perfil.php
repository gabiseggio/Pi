<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$tipo = $_SESSION['tipo'];
$foto_perfil = $_SESSION['foto_perfil'] ?? 'default.png';

// Pega bio do usuário
$stmt = $conn->prepare("SELECT bio FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$bio = $user['bio'] ?? '';

// Pega animais do usuário
if ($tipo === 'doador') {
    $sql_animais = "SELECT * FROM animais WHERE id_usuario = :id ORDER BY data_postagem DESC";
    $stmt_animais = $conn->prepare($sql_animais);
    $stmt_animais->execute([':id' => $user_id]);
    $animais = $stmt_animais->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql_animais = "SELECT a.*, f.status AS status_form
                    FROM formularios_adocao f
                    JOIN animais a ON f.id_animal = a.id
                    WHERE f.id_adotante = :id
                    ORDER BY f.data_envio DESC";
    $stmt_animais = $conn->prepare($sql_animais);
    $stmt_animais->execute([':id' => $user_id]);
    $animais = $stmt_animais->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Perfil - <?php echo htmlspecialchars($username); ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff;
    margin: 0;
    display: flex;
}
.sidebar {
    width: 200px;
    background: #f0f0f0;
    padding: 20px;
    height: 100vh;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}
.sidebar h3 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}
.sidebar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
}
.sidebar a {
    display: block;
    margin: 12px 0;
    text-decoration: none;
    color: #333;
    font-weight: bold;
}
.content {
    flex: 1;
    padding: 20px;
}
.profile-header {
    text-align: center;
    margin-bottom: 30px;
}
.profile-header img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}
.profile-header h3 {
    margin: 5px 0;
}
.profile-header p {
    font-size: 1em;
    color: #555;
}
.edit-btn {
    padding: 8px 16px;
    border: none;
    background: #007bff;
    color: #fff;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
}
.animais-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
.animal-card {
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 10px;
    width: 200px;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    text-align: center;
}
.animal-card img {
    width: 100%;
    height: 120px;
    border-radius: 10px;
    object-fit: cover;
    margin-bottom: 5px;
}
.animal-card p {
    font-size: 0.9em;
    color: #555;
    margin: 3px 0;
}
.status {
    font-weight: bold;
}
.user-type {
    color: #fff;
    font-size: 0.8em;
    font-weight: bold;
    padding: 2px 6px;
    border-radius: 6px;
    margin-left: 8px;
}
/* Cores diferentes por tipo */
.user-type.doador {
    background-color: #007bff; /* azul */
}
.user-type.adotante {
    background-color: #ff69b4; /* rosa */
}
</style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h3>
      <img src="../uploads/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto perfil">
      @<?php echo htmlspecialchars($username); ?>
      <span class="user-type <?php echo $tipo; ?>">
        <?php echo ucfirst($tipo); ?>
      </span>
    </h3>
    <a href="home.php">🏠 Início</a>
    <a href="notificacoes.php">🔔 Notificações</a>
    <a href="mensagens.php">💬 Mensagens</a>
    <a href="perfil.php">👤 Perfil</a>
    <?php if ($tipo === "doador"): ?>
      <a href="postar_animal.php">➕ Postar Animal</a>
    <?php endif; ?>
    <a href="../auth/logout.php">🚪 Sair</a>
  </div>

  <!-- Conteúdo principal -->
  <div class="content">
    <div class="profile-header">
      <img src="../uploads/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto perfil">
      <h3>
        @<?php echo htmlspecialchars($username); ?>
        <span class="user-type <?php echo $tipo; ?>">
          <?php echo ucfirst($tipo); ?>
        </span>
      </h3>
      <p><?php echo nl2br(htmlspecialchars($bio)); ?></p>
      <button class="edit-btn" onclick="window.location.href='editar_perfil.php'">Editar Perfil</button>
    </div>

    <h2><?php echo $tipo === 'doador' ? 'Meus Animais' : 'Meus Animais Adotados / Em Análise'; ?></h2>
    <div class="animais-container">
      <?php foreach($animais as $animal): ?>
        <div class="animal-card">
          <?php if(!empty($animal['foto'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($animal['foto']); ?>" alt="Foto animal">
          <?php else: ?>
            <img src="../uploads/default.png" alt="Foto animal">
          <?php endif; ?>
          <p><b><?php echo htmlspecialchars($animal['nome']); ?></b></p>
          <p><?php echo htmlspecialchars($animal['tipo']); ?> - <?php echo htmlspecialchars($animal['raca']); ?></p>
          <p class="status">
            <?php 
              if($tipo === 'doador') echo ucfirst($animal['status']);
              else echo ucfirst($animal['status_form']); 
            ?>
          </p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
