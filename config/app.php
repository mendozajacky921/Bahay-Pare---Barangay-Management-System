<?php

declare(strict_types=1);

define('APP_NAME',    $_ENV['APP_NAME']    ?? 'Barangay Management System');
define('APP_ENV',     $_ENV['APP_ENV']     ?? 'production');
define('APP_URL',     rtrim($_ENV['APP_URL'] ?? '', '/'));
define('APP_SECRET',  $_ENV['APP_SECRET']);

define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 1800));
define('SESSION_NAME',     $_ENV['SESSION_NAME'] ?? 'barangay_session');

define('BARANGAY_NAME',         $_ENV['BARANGAY_NAME']         ?? 'Barangay');
define('BARANGAY_MUNICIPALITY', $_ENV['BARANGAY_MUNICIPALITY'] ?? '');
define('BARANGAY_PROVINCE',     $_ENV['BARANGAY_PROVINCE']     ?? '');

// Supported locales
define('SUPPORTED_LOCALES', ['en', 'fil']);
define('DEFAULT_LOCALE', 'en');

// Pagination
define('ITEMS_PER_PAGE', 15);

// File upload limits
define('MAX_FILE_SIZE_MB', 5);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf']);
