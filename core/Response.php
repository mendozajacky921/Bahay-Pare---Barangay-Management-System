<?php

declare(strict_types=1);

namespace Core;

class Response
{
    public static function redirect(string $url, int $code = 302): never
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function redirectBack(string $fallback = '/'): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect($referer);
    }

    public static function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ];
        $title = $messages[$code] ?? 'Error';
        if (!$message) {
            $message = $title;
        }
        View::render('errors/' . $code, ['title' => $title, 'message' => $message]);
        exit;
    }

    public static function success(string $message, string $redirect = ''): never
    {
        Session::flash('success', $message);
        self::redirect($redirect ?: ($_SERVER['HTTP_REFERER'] ?? '/'));
    }

    public static function error(string $message, string $redirect = ''): never
    {
        Session::flash('error', $message);
        self::redirect($redirect ?: ($_SERVER['HTTP_REFERER'] ?? '/'));
    }

    public static function withErrors(array $errors, array $old = []): never
    {
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        self::redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    public static function download(string $filePath, string $fileName): never
    {
        if (!file_exists($filePath)) {
            self::abort(404);
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public static function pdf(string $content, string $fileName): never
    {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
}
