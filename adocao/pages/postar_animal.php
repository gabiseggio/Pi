<?php
session_start();
require_once("../config/connection.php");

// Só DOADOR pode postar
if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'doador') {
    header("Location: home.php");
    exit;
}

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['user_id'];
    $nome = trim($_POST['nome']);
    $idade = trim($_POST['idade']);
    $raca = trim($_POST['raca']);
    $tipo = $_POST['tipo'];
    $sexo = $_POST['sexo'];
    $vacinado = $_POST['vacinado'];
    $deficiencia = $_POST['deficiencia'];
    $localizacao = trim($_POST['localizacao']);
    $observacoes = trim($_POST['observacoes']);
    $status = $_POST['status'];

    // Upload de foto
    $foto_nome = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nome = uniqid() . "." . $ext;
        $destino = "../uploads/" . $foto_nome;

        if (!is_dir("../uploads")) {
            mkdir("../uploads", 0777, true);
        }
        move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
    }

    $sql = "INSERT INTO animais 
        (id_usuario, nome, idade, raca, tipo, sexo, vacinado, deficiencia, localizacao, foto, observacoes, status)
        VALUES (:id_usuario, :nome, :idade, :raca, :tipo, :sexo, :vacinado, :deficiencia, :localizacao, :foto, :observacoes, :status)";
    $stmt = $conn->prepare($sql);

    $ok = $stmt->execute([
        ":id_usuario" => $id_usuario,
        ":nome" => $nome,
        ":idade" => $idade,
        ":raca" => $raca,
        ":tipo" => $tipo,
        ":sexo" => $sexo,
        ":vacinado" => $vacinado,
        ":deficiencia" => $deficiencia,
        ":localizacao" => $localizacao,
        ":foto" => $foto_nome,
        ":observacoes" => $observacoes,
        ":status" => $status
    ]);

    if ($ok) {
        header("Location: home.php");
        exit;
    } else {
        $mensagem = "Erro ao postar animal.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Postar Animal</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 20px;
    }
    .container {
      width: 500px;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      background: #f9f9f9;
    }
    input, select, textarea, button {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
      /* Adicionei o box-sizing para os inputs não "grudarem" no fim do container */
      box-sizing: border-box;
      
    }
    button {
      background: linear-gradient(to right, #007bff, #ff69b4);
      color: #fff;
      font-weight: bold;
      border: none;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Postar Novo Animal</h2>
    <?php if ($mensagem): ?>
      <p style="color:red;"><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
      <input type="text" name="nome" placeholder="Nome" required>
      <input type="text" name="idade" placeholder="Idade" required>
      <input type="text" name="raca" placeholder="Raça" required>
      
      <label>Tipo:</label>
      <select name="tipo" required>
        <option value="cachorro">Cachorro</option>
        <option value="gato">Gato</option>
      </select>

      <label>Sexo:</label>
      <select name="sexo" required>
        <option value="macho">Macho</option>
        <option value="femea">Fêmea</option>
      </select>

      <label>Vacinado:</label>
      <select name="vacinado">
        <option value="1">Sim</option>
        <option value="0">Não</option>
      </select>

      <label>Possui Deficiência?</label>
      <select name="deficiencia">
        <option value="0">Não</option>
        <option value="1">Sim</option>
      </select>

      <input type="text" name="localizacao" placeholder="Localização (ex: Porto Alegre, Zona Sul)" required>
      
      <label>Foto:</label>
      <input type="file" name="foto">
      
      <!--Tirei o resize da textarea e adicionei um script para que ela expanda verticalmente conforme o tamanho da descrição que o usuário digitar. -->
      <textarea id="desc" style="resize: none;" name="observacoes" placeholder="Descrição do animal e sua situação"></textarea>

      <label>Status:</label>
      <select name="status" required>
        <option value="disponivel">Disponível</option>
        <option value="em_analise">Em Análise</option>
        <option value="adotado">Adotado</option>
      </select>

      <button type="submit">Postar Animal</button>
    </form>
  </div>

  <script>
    const textarea = document.getElementById('desc');

        textarea.addEventListener('input', () => {
            // Define a altura do textarea para 'auto' para recalcular a altura do conteúdo
            textarea.style.height = 'auto';
            // Define a nova altura com base no scrollHeight do conteúdo
            textarea.style.height = textarea.scrollHeight + 'px';
        });
  </script>
</body>
</html>
