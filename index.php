<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

function getVavoo() {
    $ua = "VAVOO/2.6";
    
    // 1. Опит за взимане на Token
    $ch = curl_init("https://vavoo.to/api/v2/auth/register");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $auth = json_decode(curl_exec($ch), true);
    $token = $auth['token'] ?? "";
    curl_close($ch);

    // 2. Взимане на списъка (само ако имаме токен или пробваме без него като последен шанс)
    $url = "https://vavoo.to/live2/index" . ($token ? "?token=" . $token : "");
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $out = [];
    if (is_array($res)) {
        foreach ($res as $c) {
            if (isset($c['group']) && stripos($c['group'], 'Bulgaria') !== false) {
                // ПРИНУДИТЕЛНО залепяме токена към всеки линк
                $c['url'] = $token ? $c['url'] . "?token=" . $token : $c['url'];
                $out[] = $c;
            }
        }
    }
    
    // Ако няма токен, връщаме грешка за дебъг
    if (!$token) {
        return json_encode(["error" => "IP Blocked by Vavoo", "data" => $out]);
    }

    return json_encode($out);
}

echo getVavoo();
