<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class ExtraSettingsService
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

    public function updateExtraSettings(array $settings, $tzConfigSetCallback, $tzConfigFinalizeCallback): void
    {
        $text = $settings['templateContents'];
        
        foreach ($settings['values'] as $key => $value) {
            $tzConfigSetCallback($text, $key, $value);
        }

        $text = $tzConfigFinalizeCallback($text);

        $tmpFile = $this->configFilePath . '.tmp';
        $fh = fopen($tmpFile, 'w');
        if (!$fh) throw new \Exception("Can't open file: " . $this->configFilePath);

        if (fwrite($fh, $text) === false) {
            fclose($fh);
            @unlink($tmpFile);
            throw new \Exception("Can't write file: " . $this->configFilePath);
        }
        fclose($fh);

        if (is_file($this->configFilePath)) {
            @copy($this->configFilePath, $this->configFilePath . '.bak');
        }

        if (!rename($tmpFile, $this->configFilePath)) {
            @unlink($tmpFile);
            throw new \Exception("Can't replace file: " . $this->configFilePath);
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
