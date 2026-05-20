<?php
if (isset($_GET['url']) && isset($_GET['filename'])) {
    $url = $_GET['url'];
    $filename = $_GET['filename'];

    // Cabeçalhos para download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Lê o arquivo e envia para o navegador
    readfile($url);
    exit;
} else {
    echo "Parâmetros inválidos.";
}
?>
