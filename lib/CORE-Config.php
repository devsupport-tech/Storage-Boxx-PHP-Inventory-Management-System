<?php
// Load environment variables
require_once __DIR__ . DIRECTORY_SEPARATOR . "CORE-Env.php";
CoreEnv::load();

// (A) HOST CONFIGURATION
define("SITE_NAME", env("APP_NAME", "Storage Boxx"));
define("HOST_BASE", env("APP_URL", "http://localhost/"));
define("HOST_NAME", parse_url(HOST_BASE, PHP_URL_HOST));
define("HOST_BASE_PATH", parse_url(HOST_BASE, PHP_URL_PATH));
define("HOST_ASSETS", HOST_BASE . "assets/");

// (B) API ENDPOINT CONFIGURATION
define("HOST_API", "api/");
define("HOST_API_BASE", HOST_BASE . HOST_API);
define("API_HTTPS", env("API_HTTPS", false));

// API CORS Configuration - support for multiple formats
$apiCors = env("API_CORS", false);
if ($apiCors === "true" || $apiCors === true) {
    define("API_CORS", true);
} elseif ($apiCors === "false" || $apiCors === false) {
    define("API_CORS", false);
} else {
    // Handle string or array values
    $corsValue = $apiCors;
    if (strpos($corsValue, ',') !== false) {
        // Convert comma-separated string to array
        $corsValue = array_map('trim', explode(',', $corsValue));
    }
    define("API_CORS", $corsValue);
}

// API Rate Limiting
define("API_RATE_LIMIT", env("API_RATE_LIMIT", 60));

// (C) DATABASE CONFIGURATION
// Primary database (MySQL for legacy support)
define("DB_HOST", env("DB_HOST", "localhost"));
define("DB_NAME", env("DB_NAME", "storageboxx"));
define("DB_CHARSET", "utf8mb4");
define("DB_USER", env("DB_USER", "root"));
define("DB_PASSWORD", env("DB_PASSWORD", ""));
define("DB_CONNECTION", env("DB_CONNECTION", "mysql"));
define("DB_PORT", env("DB_PORT", 3306));

// PostgreSQL/Supabase database configuration
define("POSTGRES_HOST", env("POSTGRES_HOST", "localhost"));
define("POSTGRES_DB", env("POSTGRES_DB", "storageboxx"));
define("POSTGRES_USER", env("POSTGRES_USER", "postgres"));
define("POSTGRES_PASSWORD", env("POSTGRES_PASSWORD", ""));
define("POSTGRES_PORT", env("POSTGRES_PORT", 5432));

// Supabase configuration
define("SUPABASE_URL", env("SUPABASE_URL", ""));
define("SUPABASE_ANON_KEY", env("SUPABASE_ANON_KEY", ""));
define("SUPABASE_SERVICE_ROLE_KEY", env("SUPABASE_SERVICE_ROLE_KEY", ""));
define("SUPABASE_PROJECT_ID", env("SUPABASE_PROJECT_ID", ""));

// (D) AUTOMATIC SYSTEM PATHS
define("PATH_LIB", __DIR__ . DIRECTORY_SEPARATOR);
define("PATH_BASE", dirname(PATH_LIB) . DIRECTORY_SEPARATOR);
define("PATH_ASSETS", PATH_BASE . "assets" . DIRECTORY_SEPARATOR);
define("PATH_PAGES", PATH_BASE . "pages" . DIRECTORY_SEPARATOR);
define("PATH_STORAGE", env("STORAGE_ROOT", PATH_BASE . "storage" . DIRECTORY_SEPARATOR));

// (E) JSON WEB TOKEN CONFIGURATION
define("JWT_ALGO", env("JWT_ALGORITHM", "HS256"));
define("JWT_EXPIRE", env("JWT_EXPIRE", 86400));
define("JWT_ISSUER", env("JWT_ISSUER", "Storage-Boxx"));
define("JWT_SECRET", env("JWT_SECRET", "change-this-secret-key-in-production"));

// (F) ERROR HANDLING CONFIGURATION
$appEnv = env("APP_ENV", "development");
$appDebug = env("APP_DEBUG", true);

if ($appEnv === "production" && !$appDebug) {
    // Production configuration
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    ini_set("error_log", PATH_BASE . "storage/logs/error.log");
    define("ERR_SHOW", false);
} else {
    // Development configuration
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set("display_errors", env("ERROR_REPORTING", true) ? 1 : 0);
    ini_set("log_errors", 1);
    ini_set("error_log", PATH_BASE . "storage/logs/error.log");
    define("ERR_SHOW", env("ERROR_REPORTING", true));
}

// (G) TIMEZONE CONFIGURATION
define("SYS_TZ", env("TIMEZONE", "UTC"));
define("SYS_TZ_OFFSET", env("TIMEZONE_OFFSET", "+00:00"));
date_default_timezone_set(SYS_TZ);

// (H) USER LEVELS CONFIGURATION
define("USR_LVL", [
    env("USER_LEVEL_ADMIN", "A") => "Admin", 
    env("USER_LEVEL_USER", "U") => "User", 
    env("USER_LEVEL_SUSPENDED", "S") => "Suspended"
]);

// (I) PUSH NOTIFICATION CONFIGURATION
define("PUSH_PUBLIC", env("PUSH_PUBLIC_KEY", ""));
define("PUSH_PRIVATE", env("PUSH_PRIVATE_KEY", ""));

// (J) CACHE CONFIGURATION
define("CACHE_DRIVER", env("CACHE_DRIVER", "redis"));
define("CACHE_PREFIX", env("CACHE_PREFIX", "storage_boxx"));

// (K) SESSION CONFIGURATION
define("SESSION_DRIVER", env("SESSION_DRIVER", "redis"));
define("SESSION_LIFETIME", env("SESSION_LIFETIME", 120));
define("SESSION_ENCRYPT", env("SESSION_ENCRYPT", false));

// (L) REDIS CONFIGURATION
define("REDIS_HOST", env("REDIS_HOST", "localhost"));
define("REDIS_PORT", env("REDIS_PORT", 6379));
define("REDIS_PASSWORD", env("REDIS_PASSWORD", ""));
define("REDIS_DB", env("REDIS_DB", 0));

// (M) MAIL CONFIGURATION
define("MAIL_DRIVER", env("MAIL_DRIVER", "smtp"));
define("MAIL_HOST", env("MAIL_HOST", "localhost"));
define("MAIL_PORT", env("MAIL_PORT", 587));
define("MAIL_USERNAME", env("MAIL_USERNAME", ""));
define("MAIL_PASSWORD", env("MAIL_PASSWORD", ""));
define("MAIL_ENCRYPTION", env("MAIL_ENCRYPTION", ""));
define("MAIL_FROM_ADDRESS", env("MAIL_FROM_ADDRESS", "noreply@localhost"));
define("MAIL_FROM_NAME", env("MAIL_FROM_NAME", SITE_NAME));

// (N) PWA CONFIGURATION  
define("PWA_NAME", env("PWA_NAME", SITE_NAME));
define("PWA_SHORT_NAME", env("PWA_SHORT_NAME", "StorageBoxx"));
define("PWA_DESCRIPTION", env("PWA_DESCRIPTION", "Modern inventory management system"));
define("PWA_THEME_COLOR", env("PWA_THEME_COLOR", "#007bff"));
define("PWA_BACKGROUND_COLOR", env("PWA_BACKGROUND_COLOR", "#ffffff"));

// (O) QR CODE CONFIGURATION
define("QR_CODE_SIZE", env("QR_CODE_SIZE", 200));
define("QR_CODE_ERROR_CORRECTION", env("QR_CODE_ERROR_CORRECTION", "M"));

// (P) NFC CONFIGURATION
define("NFC_ENABLED", env("NFC_ENABLED", true));

// (Q) SSL CONFIGURATION
define("SSL_ENABLED", env("SSL_ENABLED", false));
define("SSL_EMAIL", env("SSL_EMAIL", ""));
define("SSL_DOMAIN", env("SSL_DOMAIN", ""));

// Create storage directories if they don't exist
$storageDirs = [
    PATH_STORAGE,
    PATH_STORAGE . 'logs',
    PATH_STORAGE . 'cache',
    PATH_STORAGE . 'sessions',
    PATH_STORAGE . 'uploads'
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        // Try to create directory, but don't fail if we can't
        if (@mkdir($dir, 0755, true)) {
            error_log("Created storage directory: " . $dir);
        } else {
            error_log("Warning: Could not create storage directory: " . $dir . " (may already exist or need manual creation)");
        }
    }
}