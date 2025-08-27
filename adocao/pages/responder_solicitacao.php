<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'doador') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id_solicitacao']);
    $acao = $_POST['acao'] === 'aprovar' ? 'aprovado' : 'recusado';

    $stmt = $conn->prepare("
        UPDATE formularios_adocao f
        JOIN animais a ON f.id_animal = a.id
        SET f.status = :acao
        WHERE f.id = :id AND a.id_usuario = :user_id
    ");
    $stmt->execute([':acao'=>$acao, ':id'=>$id, ':user_id'=>$_SESSION['user_id']]);
}

header("Location: notificacoes.php");
exit;
