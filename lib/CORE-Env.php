<?php
/**
 * Environment Variable Loader for Storage Boxx
 * Loads .env file and provides helper functions for environment variables
 */

class CoreEnv {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        }

        if (!file_exists($path)) {
            // Try .env.example as fallback
            $examplePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.example';
            if (file_exists($examplePath)) {
                error_log("Warning: .env file not found, using .env.example");
                $path = $examplePath;
            } else {
                error_log("Warning: No .env file found at: " . $path);
                self::$loaded = true;
                return;
            }
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }

                // Set in $_ENV and $_SERVER superglobals
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
                
                // Store in our internal array
                self::$variables[$key] = $value;
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable with optional default value
     */
    public static function get($key, $default = null) {
        self::load();
        
        // Check in order: $_ENV, $_SERVER, internal array, getenv()
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }

    /**
     * Get environment variable as boolean
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key, $default);
        
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower($value);
        return in_array($value, ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Get environment variable as integer
     */
    public static function getInt($key, $default = 0) {
        return (int) self::get($key, $default);
    }

    /**
     * Get environment variable as float
     */
    public static function getFloat($key, $default = 0.0) {
        return (float) self::get($key, $default);
    }

    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        self::load();
        return self::get($key) !== null;
    }

    /**
     * Get all environment variables
     */
    public static function all() {
        self::load();
        return self::$variables;
    }
}

// Helper function for easier access
if (!function_exists('env')) {
    function env($key, $default = null) {
        return CoreEnv::get($key, $default);
    }
}