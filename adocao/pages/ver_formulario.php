<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'doador') {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id'])) die("Solicitação inválida!");
$id_form = intval($_GET['id']);

// Buscar dados do formulário + animal + adotante
$stmt = $conn->prepare("
    SELECT f.*, a.nome AS animal_nome, a.id_usuario AS id_doador,
           u.username AS adotante_nome, u.foto_perfil AS adotante_foto
    FROM formularios_adocao f
    JOIN animais a ON f.id_animal = a.id
    JOIN usuarios u ON f.id_adotante = u.id
    WHERE f.id = :id AND a.id_usuario = :user_id
");
$stmt->execute([':id' => $id_form, ':user_id' => $_SESSION['user_id']]);
$form = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$form) die("Solicitação não encontrada.");

// Se doador aprovou
if (isset($_POST['aprovar'])) {
    $id_formulario = $form['id'];

    // Atualizar status do formulário
    $sql = "UPDATE formularios_adocao SET status = 'aprovado' WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([":id" => $id_formulario]);

    $id_adotante = $form['id_adotante'];
    $id_animal   = $form['id_animal'];
    $id_doador   = $_SESSION['user_id'];

    // Criar chat entre doador e adotante
    $sql3 = "INSERT INTO chats (id_doador, id_adotante, id_animal) 
             VALUES (:doador, :adotante, :animal)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([
        ":doador" => $id_doador,
        ":adotante" => $id_adotante,
        ":animal" => $id_animal
    ]);
    $chat_id = $conn->lastInsertId();

    // Criar notificação para o adotante
    $sql4 = "INSERT INTO notificacoes (id_usuario, id_animal, tipo, detalhe, lida) 
             VALUES (:id_usuario, :id_animal, 'chat', 'Sua solicitação foi aprovada! Um chat foi criado com o doador.', 0)";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->execute([
        ":id_usuario" => $id_adotante,
        ":id_animal" => $id_animal
    ]);

    header("Location: ../chat/chat.php?chat_id=" . $chat_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Formulário de <?php echo htmlspecialchars($form['adotante_nome']); ?></title>
<style>
body { font-family: Arial, sans-serif; background:#fff; padding:20px; max-width:600px; margin:0 auto; }
label { font-weight:bold; display:block; margin-top:10px; }
input, textarea { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; margin-top:5px; }
img { max-width:100%; border-radius:6px; margin-top:5px; }
button { padding:10px 20px; border:none; background:#28a745; color:#fff; border-radius:6px; cursor:pointer; margin-top:15px; }
button:hover { background:#218838; }
</style>
</head>
<body>
<h2>Formulário de <?php echo htmlspecialchars($form['adotante_nome']); ?></h2>
<p><strong>Animal:</strong> <?php echo htmlspecialchars($form['animal_nome']); ?></p>
<p><strong>Maior de idade:</strong> <?php echo $form['maioridade'] ? 'Sim' : 'Não'; ?></p>
<p><strong>Endereço:</strong> <?php echo nl2br(htmlspecialchars($form['endereco'])); ?></p>
<p><strong>Telefone:</strong> <?php echo htmlspecialchars($form['telefones']); ?></p>
<p><strong>RG:</strong> <?php echo htmlspecialchars($form['rg']); ?></p>
<p><strong>CPF:</strong> <?php echo htmlspecialchars($form['cpf']); ?></p>
<p><strong>Termo Assinado:</strong> <?php echo $form['termo_assinado'] ? 'Sim' : 'Não'; ?></p>
<?php if($form['comprovante']): ?>
    <p><strong>Comprovante enviado:</strong></p>
    <img src="../uploads/<?php echo htmlspecialchars($form['comprovante']); ?>" alt="Comprovante">
<?php endif; ?>

<!-- Botão de Aprovar -->
<form method="post">
    <button type="submit" name="aprovar">Aprovar Solicitação e Criar Chat</button>
</form>
</body>
</html>

