<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Adoções</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      width: 320px;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      background: #fff;
      text-align: center;
      transition: transform 0.3s ease;
    }

    .container:hover {
      transform: translateY(-5px);
    }

    h2 {
      margin-bottom: 20px;
      background: linear-gradient(to right, #007bff, #ff69b4);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-size: 1.8rem;
    }

    input {
      width: 85%;
      padding: 10px;
      margin: 8px auto;
      display: block;
      border-radius: 10px;
      border: 1px solid #ccc;
      outline: none;
      transition: border 0.3s, box-shadow 0.3s;
      font-size: 14px;
    }

    input:focus {
      border: 1px solid #ff69b4;
      box-shadow: 0 0 6px rgba(255,105,180,0.4);
    }

    button {
      width: 90%;
      padding: 12px;
      margin-top: 15px;
      border-radius: 12px;
      border: none;
      font-weight: bold;
      font-size: 15px;
      cursor: pointer;
      background: linear-gradient(135deg, #007bff, #ff69b4);
      color: #fff;
      transition: opacity 0.3s;
    }

    button:hover {
      opacity: 0.9;
    }

    .links {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .links a {
      text-decoration: none;
      color: #007bff;
      margin: 0 5px;
      /*Acelerei um pouco para evitar a sensação de 'delay' */
      transition: color 0.2s; 
    }

    .links a:hover {
      color: #ff69b4;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Login</h2>
    <form action="auth/login.php" method="POST">
      <input type="text" name="email_username" placeholder="Email ou @username" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit">Entrar</button>
    </form>
    <div class="links">
      <a href="register.php">Cadastre-se</a> | <a href="#">Esqueci minha senha</a>
    </div>
  </div>
</body>
</html>
