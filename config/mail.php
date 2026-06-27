<?php

declare(strict_types=1);

define('MAIL_HOST',         $_ENV['MAIL_HOST']         ?? 'smtp.resend.com');
define('MAIL_PORT',         (int)($_ENV['MAIL_PORT']   ?? 465));
define('MAIL_USERNAME',     $_ENV['MAIL_USERNAME']     ?? '');
define('MAIL_PASSWORD',     $_ENV['MAIL_PASSWORD']     ?? '');
define('MAIL_ENCRYPTION',   $_ENV['MAIL_ENCRYPTION']   ?? 'ssl');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com');
define('MAIL_FROM_NAME',    $_ENV['MAIL_FROM_NAME']    ?? APP_NAME);
