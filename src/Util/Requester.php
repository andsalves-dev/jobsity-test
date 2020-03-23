<?php

namespace App\Util;

class Requester {
    public static function makeRequest(string $url, array $queryParams = [], array $data = [], string $method = 'GET') {
        $queryParamsStr = http_build_query($queryParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "$url?$queryParamsStr");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        if (!in_array($method, ['GET', 'POST'])) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}