<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class CronSettingsService
{
    private IDbConnection $db;
    private string $tbPrefix;
    private string $configFilePath;

    public function __construct(IDbConnection $db, string $tbPrefix, string $configFilePath)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
        $this->configFilePath = $configFilePath;
    }

    public function updateCronSettings(array $settings): void
    {
        $loop = max(0, min(3300, (int)($settings['loop'] ?? 300)));
        $tick = max(15, min(900, (int)($settings['tick'] ?? 60)));
        if ($loop > 0 && $loop < $tick) {
            $loop = 0;
        }
        $cleanReports  = max(0, min(3650, (int)($settings['cleanReports'] ?? 14)));
        $cleanChat     = max(0, min(3650, (int)($settings['cleanChat'] ?? 7)));
        $cleanMessages = max(0, min(3650, (int)($settings['cleanMessages'] ?? 0)));
        $cronKey       = (string)($settings['cronKey'] ?? '');

        $config = @file_get_contents($this->configFilePath);
        if ($config === false) {
            throw new \Exception("Can't read file: " . $this->configFilePath);
        }

        $missing = [];
        
        $this->updateDefine($config, 'CRON_LOOP_SECONDS', (string)$loop, $missing);
        $this->updateDefine($config, 'CRON_TICK_SECONDS', (string)$tick, $missing);
        $this->updateDefine($config, 'CRON_KEY', "'" . addcslashes($cronKey, "'\\") . "'", $missing);
        $this->updateDefine($config, 'CLEANUP_REPORTS_DAYS', (string)$cleanReports, $missing);
        $this->updateDefine($config, 'CLEANUP_CHAT_DAYS', (string)$cleanChat, $missing);
        $this->updateDefine($config, 'CLEANUP_MESSAGES_DAYS', (string)$cleanMessages, $missing);

        if (!empty($missing)) {
            $anchorPattern = "/define\s*\(\s*(['\"])AUTOMATION_LOCK_FILE_NAME\\1\s*,.*?\)\s*;/s";
            $block = "\n\n//////////////////////////////////\n"
                   . "// *****  CRON / AUTOMATION *****//\n"
                   . "//////////////////////////////////\n"
                   . implode("\n", $missing);

            if (preg_match($anchorPattern, $config, $anchor)) {
                $config = str_replace($anchor[0], $anchor[0] . $block, $config);
            } else {
                $config = preg_replace('/\?>\s*$/', '', $config) . $block . "\n";
            }
        }

        $fh = @fopen($this->configFilePath, 'w');
        if (!$fh) throw new \Exception("Can't open file: " . $this->configFilePath);
        fwrite($fh, $config);
        fclose($fh);
    }

    private function updateDefine(&$text, $constant, $rawValue, &$missing)
    {
        $pattern = "/define\s*\(\s*(['\"])" . preg_quote($constant, '/') . "\\1\s*,.*?\)\s*;/s";
        if (preg_match($pattern, $text, $m)) {
            $text = str_replace($m[0], "define('" . $constant . "', " . $rawValue . ");", $text);
        } else {
            $missing[] = "define('" . $constant . "', " . $rawValue . ");";
        }
    }

    public function logAdminAction(int $admid, string $logText): void
    {
        $time = time();
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, " . (int)$admid . ", '$logEsc', $time)"
        );
    }
}
