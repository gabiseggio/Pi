<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$chat_id = $_GET['chat_id'] ?? null;

if (!$chat_id) {
    die("Chat inválido.");
}

// Verificar se o usuário faz parte do chat
$sql = "SELECT * FROM chats WHERE id = :id AND (id_doador = :uid OR id_adotante = :uid)";
$stmt = $conn->prepare($sql);
$stmt->execute([":id" => $chat_id, ":uid" => $user_id]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    die("Você não tem acesso a este chat.");
}

// Buscar mensagens
$sql = "SELECT m.*, u.username 
        FROM mensagens m
        JOIN usuarios u ON u.id = m.id_remetente
        WHERE id_chat = :chat_id ORDER BY data_envio ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([":chat_id" => $chat_id]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enviar nova mensagem
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mensagem'])) {
    $msg = trim($_POST['mensagem']);
    if ($msg != "") {
        $sql = "INSERT INTO mensagens (id_chat, id_remetente, mensagem) VALUES (:chat, :rem, :msg)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ":chat" => $chat_id,
            ":rem" => $user_id,
            ":msg" => $msg
        ]);
        header("Location: chat.php?chat_id=" . $chat_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Chat</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Chat com <?= ($user_id == $chat['id_doador'] ? "Adotante" : "Doador") ?></h2>

    <div class="chat-box" style="border:1px solid #ccc; padding:10px; height:400px; overflow-y:scroll;">
        <?php foreach ($mensagens as $m): ?>
            <p><strong><?= htmlspecialchars($m['username']) ?>:</strong> <?= htmlspecialchars($m['mensagem']) ?> <small>(<?= $m['data_envio'] ?>)</small></p>
        <?php endforeach; ?>
    </div>

    <form method="post">
        <input type="text" name="mensagem" placeholder="Digite sua mensagem..." required>
        <button type="submit">Enviar</button>
    </form>
</body>
</html>
