<?php

if (($_POST['pm_ajax'] ?? null) !== '1' || isset($_POST['exportar_csv'])) {
    http_response_code(400);
    exit('Invalid matrix AJAX request.');
}

require __DIR__ . '/../front/matrix.result.php';
