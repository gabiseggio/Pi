<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'doador') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Pega todas as solicitações de adoção para os animais do doador
$stmt = $conn->prepare("
    SELECT f.*, a.nome AS animal_nome, u.username AS adotante_nome, u.foto_perfil AS adotante_foto
    FROM formularios_adocao f
    JOIN animais a ON f.id_animal = a.id
    JOIN usuarios u ON f.id_adotante = u.id
    WHERE a.id_usuario = :user_id
    ORDER BY f.data_envio DESC
");
$stmt->execute([':user_id' => $user_id]);
$solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Notificações</title>
<style>
body { font-family: Arial, sans-serif; background: #fff; margin:0; display:flex; }
.sidebar {
    width: 200px; background: #f0f0f0; padding: 20px; height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}
.sidebar a { display:block; margin:12px 0; text-decoration:none; color:#333; font-weight:bold; }
.content { flex:1; padding:20px; }
.solicitacao-card {
    border:1px solid #ddd; border-radius:12px; padding:10px; margin-bottom:15px; box-shadow:0 0 5px rgba(0,0,0,0.1);
}
.solicitacao-card img { width:50px; height:50px; border-radius:50%; object-fit:cover; margin-right:10px; vertical-align:middle; }
button { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; margin-right:5px; }
button.aprovar { background:#28a745; color:#fff; }
button.recusar { background:#dc3545; color:#fff; }
a.ver-form { text-decoration:none; color:#007bff; font-weight:bold; margin-left:5px; }
</style>
</head>
<body>
<div class="sidebar">
    <a href="home.php">🏠 Início</a>
    <a href="perfil.php">👤 Perfil</a>
    <a href="../auth/logout.php">🚪 Sair</a>
</div>

<div class="content">
<h2>Solicitações de Adoção</h2>

<?php if(!$solicitacoes): ?>
    <p>Nenhuma solicitação por enquanto.</p>
<?php else: ?>
    <?php foreach($solicitacoes as $sol): ?>
        <div class="solicitacao-card">
            <img src="../uploads/<?php echo htmlspecialchars($sol['adotante_foto'] ?? 'default.png'); ?>" alt="Foto adotante">
            <strong><?php echo htmlspecialchars($sol['adotante_nome']); ?></strong> solicitou adotar <strong><?php echo htmlspecialchars($sol['animal_nome']); ?></strong><br>
            Status: <strong><?php echo ucfirst($sol['status']); ?></strong>
            <div style="margin-top:5px;">
                <a class="ver-form" href="ver_formulario.php?id=<?php echo $sol['id']; ?>" target="_blank">Ver Formulário</a>
                <?php if($sol['status'] === 'aguardando'): ?>
                    <form action="responder_solicitacao.php" method="post" style="display:inline;">
                        <input type="hidden" name="id_solicitacao" value="<?php echo $sol['id']; ?>">
                        <button type="submit" name="acao" value="aprovar" class="aprovar">Aprovar</button>
                        <button type="submit" name="acao" value="recusar" class="recusar">Recusar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
</div>
</body>
</html>
