<?php

declare(strict_types=1);

namespace Core;

abstract class Controller
{
    protected Request $request;

    protected function render(string $view, array $data = [], ?string $layout = null): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $url): never
    {
        Response::redirect($url);
    }

    protected function success(string $message, string $redirect = ''): never
    {
        Response::success($message, $redirect);
    }

    protected function error(string $message, string $redirect = ''): never
    {
        Response::error($message, $redirect);
    }

    protected function withErrors(array $errors, array $old = []): never
    {
        Response::withErrors($errors, $old);
    }

    protected function json(mixed $data, int $code = 200): never
    {
        Response::json($data, $code);
    }

    protected function abort(int $code, string $message = ''): never
    {
        Response::abort($code, $message);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value      = $data[$field] ?? null;
            $label      = ucfirst(str_replace('_', ' ', $field));

            foreach ($fieldRules as $rule) {
                if ($rule === 'required' && ($value === null || $value === '')) {
                    $errors[$field] = "{$label} is required.";
                    break;
                }
                if ($rule === 'email' && $value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "{$label} must be a valid email address.";
                    break;
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int)substr($rule, 4);
                    if ($value !== null && strlen($value) < $min) {
                        $errors[$field] = "{$label} must be at least {$min} characters.";
                        break;
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int)substr($rule, 4);
                    if ($value !== null && strlen($value) > $max) {
                        $errors[$field] = "{$label} must not exceed {$max} characters.";
                        break;
                    }
                }
                if ($rule === 'numeric' && $value !== null && $value !== '' && !is_numeric($value)) {
                    $errors[$field] = "{$label} must be a number.";
                    break;
                }
                if ($rule === 'date' && $value && !strtotime($value)) {
                    $errors[$field] = "{$label} must be a valid date.";
                    break;
                }
                if (str_starts_with($rule, 'in:')) {
                    $options = explode(',', substr($rule, 3));
                    if ($value && !in_array($value, $options, true)) {
                        $errors[$field] = "{$label} contains an invalid value.";
                        break;
                    }
                }
            }
        }
        return $errors;
    }
}
