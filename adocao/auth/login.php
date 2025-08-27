<?php
session_start();
require_once("../config/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_username = trim($_POST['email_username']);
    $senha = $_POST['senha'];

    if (empty($email_username) || empty($senha)) {
        die("Preencha todos os campos!");
    }

    $sql = "SELECT * FROM usuarios WHERE email = :user OR username = :user LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":user", $email_username);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['tipo'] = $user['tipo'];

            header("Location: ../pages/home.php");
            exit;
        } else {
            die("Senha incorreta!");
        }
    } else {
        die("Usuário não encontrado!");
    }
} else {
    header("Location: ../index.php");
    exit;
}
