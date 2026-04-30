<?php
class ApiService {
    public static function get(string $endpoint) {
        $url = API_BASE . '/' . ltrim($endpoint, '/');
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) return ['__curl_error' => $err];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ['__raw' => $raw];
    }

    public static function post(string $endpoint, array $body) {
        $url = API_BASE . '/' . ltrim($endpoint, '/');
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);
        if ($err) return ['__curl_error' => $err];
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ['__raw' => $raw];
    }
}
