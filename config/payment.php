<?php

declare(strict_types=1);

define('PAYMONGO_PUBLIC_KEY',      $_ENV['PAYMONGO_PUBLIC_KEY']      ?? '');
define('PAYMONGO_SECRET_KEY',      $_ENV['PAYMONGO_SECRET_KEY']      ?? '');
define('PAYMONGO_WEBHOOK_SECRET',  $_ENV['PAYMONGO_WEBHOOK_SECRET']  ?? '');
define('PAYMONGO_API_URL',         'https://api.paymongo.com/v1');
