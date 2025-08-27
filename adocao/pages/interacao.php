<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não logado']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_animal = $_POST['id_animal'] ?? null;
    $tipo = $_POST['tipo'] ?? null; // 'curtida' ou 'comentario'
    $comentario_texto = $_POST['comentario_texto'] ?? null;

    if (!$id_animal || !$tipo) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
        exit;
    }

    if ($tipo === 'curtida') {
        // Verifica se já curtiu
        $check = $conn->prepare("SELECT id FROM interacoes WHERE id_usuario = :user AND id_animal = :animal AND tipo='curtida'");
        $check->execute([':user' => $user_id, ':animal' => $id_animal]);
        if ($check->rowCount() > 0) {
            // Descurtir
            $del = $conn->prepare("DELETE FROM interacoes WHERE id_usuario = :user AND id_animal = :animal AND tipo='curtida'");
            $del->execute([':user' => $user_id, ':animal' => $id_animal]);
            echo json_encode(['sucesso' => true, 'acao' => 'descurtir']);
        } else {
            // Curtir
            $stmt = $conn->prepare("INSERT INTO interacoes (id_usuario, id_animal, tipo) VALUES (:user, :animal, 'curtida')");
            $stmt->execute([':user' => $user_id, ':animal' => $id_animal]);
            echo json_encode(['sucesso' => true, 'acao' => 'curtir']);
        }
        exit;
    }

    if ($tipo === 'comentario') {
        if (!$comentario_texto) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Comentário vazio']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO interacoes (id_usuario, id_animal, tipo, comentario_texto) VALUES (:user, :animal, 'comentario', :texto)");
        $stmt->execute([':user' => $user_id, ':animal' => $id_animal, ':texto' => $comentario_texto]);
        echo json_encode(['sucesso' => true, 'mensagem' => $comentario_texto]);
        exit;
    }

    echo json_encode(['sucesso' => false, 'mensagem' => 'Tipo inválido']);
    exit;
}
?>
