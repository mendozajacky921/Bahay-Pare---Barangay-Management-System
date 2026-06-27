<?php

declare(strict_types=1);

define('SUPABASE_URL',              $_ENV['SUPABASE_URL']);
define('SUPABASE_ANON_KEY',         $_ENV['SUPABASE_ANON_KEY']);
define('SUPABASE_SERVICE_ROLE_KEY', $_ENV['SUPABASE_SERVICE_ROLE_KEY']);
define('SUPABASE_REST_URL',         SUPABASE_URL . '/rest/v1');
define('SUPABASE_AUTH_URL',         SUPABASE_URL . '/auth/v1');
define('SUPABASE_STORAGE_URL',      SUPABASE_URL . '/storage/v1');
