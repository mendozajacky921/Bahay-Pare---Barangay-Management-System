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

    /**
     * CRITICAL-03 fix: HTTP_REFERER is attacker-controllable. Previously this
     * blindly redirected to whatever Referer header was present, allowing an
     * open redirect (e.g. a crafted link/form pointing at this site with a
     * spoofed Referer could bounce an authenticated user to an external
     * phishing page after login/logout/form submission). Now we only trust
     * the referer if it points back to our own app — otherwise we fall back
     * to a safe default.
     */
    public static function redirectBack(string $fallback = '/'): never
    {
        self::redirect(self::safeRedirectTarget($fallback));
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
        // HIGH-06: clear any buffered output before rendering the error view,
        // otherwise partially-rendered page content can leak before the error
        // page, producing a garbled response.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

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
        self::redirect($redirect ?: self::safeRedirectTarget('/'));
    }

    public static function error(string $message, string $redirect = ''): never
    {
        Session::flash('error', $message);
        self::redirect($redirect ?: self::safeRedirectTarget('/'));
    }

    public static function withErrors(array $errors, array $old = []): never
    {
        Session::flash('errors', $errors);
        Session::flash('old', $old);
        self::redirect(self::safeRedirectTarget('/'));
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

    /**
     * Only trust HTTP_REFERER when it points back at our own application.
     * Anything else (missing, malformed, or pointing off-site) falls back
     * to the given default. This is what closes the open-redirect hole.
     */
    private static function safeRedirectTarget(string $fallback): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';

        if ($referer === '' || $fallback === '') {
            return $fallback;
        }

        $appHost      = parse_url(APP_URL, PHP_URL_HOST);
        $refererHost  = parse_url($referer, PHP_URL_HOST);

        if ($appHost && $refererHost && strcasecmp($appHost, $refererHost) === 0) {
            return $referer;
        }

        return $fallback;
    }
}
