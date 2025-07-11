<?php
// public/sign-message.php

// 1) Diga ao navegador que vamos devolver texto simples
header('Content-Type: text/plain');

// 2) Aponte para sua chave privada
//    Aqui __DIR__ já é o caminho para public/
$keyPath = __DIR__ . '/assets/qz-certs/private-key.pem';

// 3) Leia o parâmetro "request" que o JS vai enviar
$data = isset($_GET['request']) ? $_GET['request'] : '';

// 4) Carregue a chave privada
$privPem = file_get_contents($keyPath);
$privateKey = openssl_pkey_get_private($privPem);
if (!$privateKey) {
    http_response_code(500);
    echo "Erro ao carregar a chave privada";
    exit;
}

// 5) Assine os dados com SHA-512
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA512);

// 6) Retorne a assinatura em Base64
echo base64_encode($signature);
