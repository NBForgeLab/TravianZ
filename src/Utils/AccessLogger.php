<?php
declare(strict_types=1);



namespace App\Utils;

/**
 * Logs all user access (URLs, REQUEST data, Cookies...)
 * into a file. Usually used in hostings that do not provide
 * web server access logs, let alone ones that include
 * POST data and Cookies.
 *
 * @author martinambrus
 */
class AccessLogger
{
    private static function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);
        if (in_array($key, ['phpsessid', 'sessid', 'password', 'pass', 'pwd', 'token', 'csrf', 'auth', 'apikey', 'api_key', 'secret'], true)) {
            return true;
        }

        foreach (['pass', 'token', 'secret', 'csrf', 'auth', 'session'] as $needle) {
            if (str_contains($key, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function scalarToString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value === null) {
            return '';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($encoded) ? $encoded : '';
    }

    private static function sanitizeValue(string $value, int $maxLen = 256): string
    {
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = trim($value);
        if (strlen($value) > $maxLen) {
            return substr($value, 0, $maxLen) . '…';
        }

        return $value;
    }

    private static function formatKeyValue(string $key, mixed $value): string
    {
        if (self::isSensitiveKey($key)) {
            return $key . '=[REDACTED]';
        }

        $value = self::sanitizeValue(self::scalarToString($value));
        return $key . '=' . $value;
    }

    /**
     * Logs current request into a file defined via config constant.
     */
    public static function logRequest(): bool
    {
        try {
            if (defined('LOG_PAGE_ACCESS') && LOG_PAGE_ACCESS) {
                // go max 5 levels up - we don't have folders that go deeper than that
                $autoprefix = '';
                for ($i = 0; $i < 5; $i++) {
                    $autoprefix = str_repeat('../', $i);
                    if (file_exists($autoprefix.'autoloader.php')) {
                        // we have our path, let's leave
                        break;
                    }
                }

                // determine log file name
                $fname = $autoprefix.'var/log/'.(defined('PAGE_ACCESS_LOG_FILENAME') ? PAGE_ACCESS_LOG_FILENAME : 'access.log');

                // prepare a prefix for the log record
                $prefix = [];

                // add date
                if (!defined('PAGE_ACCESS_LOG_DATE') || (defined('PAGE_ACCESS_LOG_DATE') && PAGE_ACCESS_LOG_DATE)) {
                    $prefix[] = date('j.m.Y H:i:s');
                }

                // add IP
                if (!defined('PAGE_ACCESS_LOG_IP') || (defined('PAGE_ACCESS_LOG_IP') && PAGE_ACCESS_LOG_IP)) {
                    $prefix[] = $_SERVER['REMOTE_ADDR'];
                }

                // add the actual file name
                $prefix[] = $_SERVER['PHP_SELF'];

                // make prefix a string
                $prefix = implode(' ', $prefix);

                // add cookie info
                if (count($_COOKIE)) {
                    $out = [];
                    foreach ($_COOKIE as $key => $value) {
                        $out[] = self::formatKeyValue((string) $key, $value);
                    }

                    // write the log line
                    $cookie = implode('&', $out);
                } else {
                    $cookie = '';
                }

                // add GET info
                if (count($_GET)) {
                    $out = [];
                    foreach ($_GET as $key => $value) {
                        $out[] = self::formatKeyValue((string) $key, $value);
                    }

                    $get_info = '?'.implode('&', $out);
                } else {
                    $get_info = '';
                }

                // write the log line
                $ok1 = @file_put_contents($fname, $prefix . $get_info . "\t" . $cookie . "\n", FILE_APPEND);
                $success = ($ok1 !== false);

                // add POST info
                if (count($_POST)) {
                    $out = [];
                    foreach ($_POST as $key => $value) {
                        $out[] = self::formatKeyValue((string) $key, $value);
                    }

                    // write the log line
                    $ok2 = @file_put_contents($fname, '[POSTDATA] ' . implode('&', $out) . "\n", FILE_APPEND);
                    $success = $success && ($ok2 !== false);
                }
            }

            return isset($success) ? $success : true;
        } catch (\Exception $e) {
            // we shouldn't raise exceptions if we can't log for some reason
            // but we definitelly should return false
            return false;
        }
    }
}
