<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro - Adoções</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      width: 320px;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
      background: #f9f9f9;
      text-align: center;
    }
    h2 {
      margin-bottom: 15px;
      background: linear-gradient(to right, #007bff, #ff69b4);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    input, select {
      width: 85%;
      padding: 10px;
      margin: 8px auto;
      border-radius: 8px;
      border: 1px solid #ccc;
      display: block;
      font-size: 14px;
    }
    select {
      background: #fff;
    }
    button {
      width: 90%;
      padding: 12px;
      margin-top: 12px;
      border-radius: 10px;
      border: none;
      font-weight: bold;
      font-size: 15px;
      cursor: pointer;
      background: linear-gradient(to right, #007bff, #ff69b4);
      color: #fff;
      transition: opacity 0.3s;
    }
    button:hover {
      opacity: 0.9;
    }
    .links {
      text-align: center;
      margin-top: 12px;
      font-size: 14px;
    }
    .links a {
      text-decoration: none;
      color: #007bff;
      transition: color 0.3s;
    }
    .links a:hover {
      color: #ff69b4;
    }
    label {
      display: block;
      margin-top: 10px;
      margin-bottom: 5px;
      text-align: left;
      width: 85%;
      margin-left: auto;
      margin-right: auto;
      font-size: 14px;
      color: #444;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Cadastro</h2>
    <form action="auth/register.php" method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="username" placeholder="@username" required>
      <input type="password" name="senha" placeholder="Senha" required>
      
      <label for="tipo">Tipo de conta:</label>
      <select name="tipo" id="tipo" required>
        <option value="">Selecione...</option>
        <option value="doador">Doador</option>
        <option value="adotante">Adotante</option>
      </select>
      
      <button type="submit">Cadastrar</button>
    </form>
    <div class="links">
      <a href="index.php">Já tenho uma conta</a>
    </div>
  </div>
</body>
</html>
