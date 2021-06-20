<?php
class Message
{
    public static function output($success, $status, $message, $extra = [])
    {
        http_response_code($status);
        return array_merge([
            'success' => $success,
            'status' => $status,
            'message' => $message
        ], $extra);
    }
}
