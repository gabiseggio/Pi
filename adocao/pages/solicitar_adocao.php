<?php
session_start();
require_once("../config/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'adotante') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$mensagem = '';

if (!isset($_GET['id_animal'])) {
    die("Animal inválido!");
}

$id_animal = intval($_GET['id_animal']);

// Pega dados do animal
$stmt = $conn->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->execute([':id' => $id_animal]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    die("Animal não encontrado!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maioridade = isset($_POST['maioridade']) ? 1 : 0;
    $endereco = trim($_POST['endereco']);
    $telefone = trim($_POST['telefone']);
    $rg = trim($_POST['rg']);
    $cpf = trim($_POST['cpf']);
    $termo = isset($_POST['termo']) ? 1 : 0;

    // Upload de comprovante
    $comprovante_path = '';
    if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['comprovante']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'comprovante_'.$user_id.'_'.time().'.'.$ext;
        $caminho = '../uploads/'.$nomeArquivo;
        if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $caminho)) {
            $comprovante_path = $nomeArquivo;
        }
    }

    // Inserir no banco
    $stmt_insert = $conn->prepare("
        INSERT INTO formularios_adocao
        (id_animal, id_adotante, maioridade, endereco, telefones, rg, cpf, comprovante, termo_assinado)
        VALUES
        (:id_animal, :id_adotante, :maioridade, :endereco, :telefone, :rg, :cpf, :comprovante, :termo)
    ");

    $stmt_insert->execute([
        ':id_animal' => $id_animal,
        ':id_adotante' => $user_id,
        ':maioridade' => $maioridade,
        ':endereco' => $endereco,
        ':telefone' => $telefone,
        ':rg' => $rg,
        ':cpf' => $cpf,
        ':comprovante' => $comprovante_path,
        ':termo' => $termo
    ]);

    $mensagem = "Solicitação enviada com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Solicitar Adoção - <?php echo htmlspecialchars($animal['nome']); ?></title>
<style>
body { font-family: Arial, sans-serif; background: #fff; padding: 20px; }
.container { max-width: 500px; margin: 0 auto; }
label { display: block; margin-top: 10px; font-weight: bold; }
input[type="text"], input[type="file"], textarea { width: 100%; padding: 8px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
button { margin-top: 15px; padding: 10px; background: #007bff; color: #fff; border: none; border-radius: 8px; cursor: pointer; }
.mensagem { color: green; font-weight: bold; margin-bottom: 10px; text-align: center; }
</style>
</head>
<body>
<div class="container">
<h2>Solicitar Adoção: <?php echo htmlspecialchars($animal['nome']); ?></h2>
<?php if($mensagem): ?>
    <p class="mensagem"><?php echo $mensagem; ?></p>
<?php endif; ?>
<form action="" method="post" enctype="multipart/form-data">
    <label><input type="checkbox" name="maioridade" required> Sou maior de idade</label>
    <label>Endereço fixo:</label>
    <textarea name="endereco" required></textarea>
    <label>Telefone de contato:</label>
    <input type="text" name="telefone" required>
    <label>RG:</label>
    <input type="text" name="rg" required>
    <label>CPF:</label>
    <input type="text" name="cpf" required>
    <label>Comprovante de residência / RG / CPF (arquivo):</label>
    <input type="file" name="comprovante" accept="image/*,application/pdf" required>
    <label><input type="checkbox" name="termo" required> Me responsabilizo pelo animal e permito monitoramento mínimo de 6 meses</label>
    <button type="submit">Enviar Solicitação</button>
</form>
</div>
</body>
</html>
