<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Pega dados atuais
$stmt = $conn->prepare("SELECT foto_perfil, bio FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$foto_perfil = $user['foto_perfil'] ?? 'default.png';
$bio = $user['bio'] ?? '';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_bio = $_POST['bio'] ?? '';

    // Atualiza bio
    $stmt = $conn->prepare("UPDATE usuarios SET bio = :bio WHERE id = :id");
    $stmt->execute([':bio' => $nova_bio, ':id' => $user_id]);
    $_SESSION['bio'] = $nova_bio;

    // Upload de foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'perfil_'.$user_id.'_'.time().'.'.$ext;
        $caminho = '../uploads/'.$nomeArquivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
            // Atualiza banco
            $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id = :id");
            $stmt->execute([':foto' => $nomeArquivo, ':id' => $user_id]);
            $_SESSION['foto_perfil'] = $nomeArquivo;
        }
    }

    $mensagem = "Perfil atualizado com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Perfil</title>
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
h2 {
    text-align: center;
}
form {
    max-width: 400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}
input[type="file"] {
    padding: 5px;
}
textarea {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    resize: none;
}
button {
    padding: 10px;
    border: none;
    border-radius: 8px;
    background: #007bff;
    color: #fff;
    cursor: pointer;
}
.mensagem {
    text-align: center;
    color: green;
    font-weight: bold;
}
</style>
</head>
<body>
<div class="sidebar">
    <h3>
      <img src="../uploads/<?php echo htmlspecialchars($foto_perfil); ?>" alt="Foto perfil">
      @<?php echo $_SESSION['username']; ?>
    </h3>
    <a href="home.php">🏠 Início</a>
    <a href="notificacoes.php">🔔 Notificações</a>
    <a href="mensagens.php">💬 Mensagens</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../auth/logout.php">🚪 Sair</a>
</div>

<div class="content">
<h2>Editar Perfil</h2>
<?php if($mensagem): ?>
    <p class="mensagem"><?php echo $mensagem; ?></p>
<?php endif; ?>
<form action="" method="post" enctype="multipart/form-data">
    <label>Foto de Perfil:</label>
    <input type="file" name="foto" accept="image/*">

    <label>Bio:</label>
    <textarea name="bio" rows="4"><?php echo htmlspecialchars($bio); ?></textarea>

    <button type="submit">Salvar Alterações</button>
</form>
</div>
</body>
</html>