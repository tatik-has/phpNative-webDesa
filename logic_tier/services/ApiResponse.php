<?php

class ApiResponse
{
    private static string $version = '1.0';

    public static function success(mixed $data = null, string $message = 'Berhasil.', int $code = 200): void
    {
        http_response_code($code);
        echo json_encode([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
            'meta'    => self::meta(),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(int $code, string $message, ?array $errors = null): void
    {
        http_response_code($code);
        $body = [
            'status'  => 'error',
            'message' => $message,
            'meta'    => self::meta(),
        ];
        if ($errors !== null) {
            $body['errors'] = $errors;
        }
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function list(array $items, int $total, string $message = 'Data berhasil diambil.'): void
    {
        http_response_code(200);
        echo json_encode([
            'status'  => 'success',
            'message' => $message,
            'data'    => $items,
            'meta'    => array_merge(self::meta(), [
                'total' => $total,
                'count' => count($items),
            ]),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private static function meta(): array
    {
        return [
            'timestamp' => date('c'),
            'version'   => self::$version,
        ];
    }
}