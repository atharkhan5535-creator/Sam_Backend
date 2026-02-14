<?php

class Request
{
    public static function getBody()
    {
        return json_decode(file_get_contents("php://input"), true);
    }

    public static function getBearerToken()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) return null;

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }
}
