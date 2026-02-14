<?php

class JWT
{
    public static function generate($payload)
    {
        $header = ['alg' => JWT_ALGO, 'typ' => 'JWT'];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRY_SECONDS;

        $base64Header = self::base64UrlEncode(json_encode($header));
        $base64Payload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function verify($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;

        $validSignature = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payload", JWT_SECRET, true)
        );

        if (!hash_equals($validSignature, $signature)) return false;

        $decodedPayload = json_decode(base64_decode($payload), true);

        if ($decodedPayload['exp'] < time()) return false;

        return $decodedPayload;
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
