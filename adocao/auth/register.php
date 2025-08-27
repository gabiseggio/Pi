<?php
session_start();
require_once("../config/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo'];

    if (empty($email) || empty($username) || empty($senha) || empty($tipo)) {
        die("Preencha todos os campos!");
    }

    // Verifica se já existe username ou email
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = :email OR username = :username");
    $check->execute([":email" => $email, ":username" => $username]);

    if ($check->rowCount() > 0) {
        die("Já existe uma conta com este email ou username!");
    }

    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (email, username, senha, tipo) 
            VALUES (:email, :username, :senha, :tipo)";
    $stmt = $conn->prepare($sql);
    $ok = $stmt->execute([
        ":email" => $email,
        ":username" => $username,
        ":senha" => $senha_hash,
        ":tipo" => $tipo
    ]);

    if ($ok) {
        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['tipo'] = $tipo;

        header("Location: ../pages/home.php");
        exit;
    } else {
        die("Erro ao cadastrar usuário.");
    }
} else {
    header("Location: ../register.php");
    exit;
}
