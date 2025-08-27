<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'adotante') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Pega todas as solicitações do adotante que já foram respondidas
$stmt = $conn->prepare("
    SELECT f.*, a.nome AS animal_nome, u.username AS doador_nome, u.foto_perfil AS doador_foto
    FROM formularios_adocao f
    JOIN animais a ON f.id_animal = a.id
    JOIN usuarios u ON a.id_usuario = u.id
    WHERE f.id_adotante = :user_id AND f.status != 'aguardando'
    ORDER BY f.data_envio DESC
");
$stmt->execute([':user_id' => $user_id]);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Notificações</title>
<style>
body { font-family: Arial, sans-serif; background:#fff; margin:0; display:flex; }
.sidebar { width:200px; background:#f0f0f0; padding:20px; height:100vh; box-shadow:2px 0 5px rgba(0,0,0,0.1); }
.sidebar a { display:block; margin:12px 0; text-decoration:none; color:#333; font-weight:bold; }
.content { flex:1; padding:20px; }
.notificacao-card { border:1px solid #ddd; border-radius:12px; padding:10px; margin-bottom:15px; box-shadow:0 0 5px rgba(0,0,0,0.1); display:flex; align-items:center; }
.notificacao-card img { width:50px; height:50px; border-radius:50%; object-fit:cover; margin-right:10px; }
.status { font-weight:bold; margin-left:auto; padding:4px 8px; border-radius:6px; color:#fff; }
.status.aprovado { background:#28a745; }
.status.recusado { background:#dc3545; }
</style>
</head>
<body>
<div class="sidebar">
    <a href="home.php">🏠 Início</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../auth/logout.php">🚪 Sair</a>
</div>

<div class="content">
<h2>Notificações</h2>

<?php if(!$notificacoes): ?>
    <p>Nenhuma notificação por enquanto.</p>
<?php else: ?>
    <?php foreach($notificacoes as $n): ?>
        <div class="notificacao-card">
            <img src="../uploads/<?php echo htmlspecialchars($n['doador_foto'] ?? 'default.png'); ?>" alt="Doador">
            <div>
                <strong><?php echo htmlspecialchars($n['doador_nome']); ?></strong> respondeu à sua solicitação de <strong><?php echo htmlspecialchars($n['animal_nome']); ?></strong>
            </div>
            <span class="status <?php echo $n['status']; ?>">
                <?php echo ucfirst($n['status']); ?>
            </span>
            <a href="ver_formulario.php?id=<?php echo $n['id']; ?>" target="_blank" style="margin-left:10px;">Ver formulário</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>
</body>
</html>
