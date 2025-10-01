<?php
function create_jwt($payload) {
    $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', "$header.$payload", "SECRET_KEY", true);
    $signature = base64_encode($signature);
    return "$header.$payload.$signature";
}
