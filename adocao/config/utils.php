<?php
function resposta($sucesso, $mensagem, $extra = []) {
    echo json_encode(array_merge([
        "sucesso" => $sucesso,
        "mensagem" => $mensagem
    ], $extra));
    exit;
}
