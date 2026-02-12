<?php

declare(strict_types=1);

namespace App\Legacy;

final class BbCodeRenderer
{
    public static function render(string $input, BbCodeDatabase $database, BbCodeGenerator $generator): string
    {
        $input = preg_replace_callback(
            '/\\[alliance(\\d{0,20})\\]([^\\]]*)\\[\\/alliance\\d{0,20}\\]/is',
            static function (array $matches) use ($database): string {
                $aname = $database->getAllianceName($matches[2]);
                if (!empty($aname)) {
                    return "<a href=allianz.php?aid={$matches[2]}>{$aname}</a>";
                }
                return 'Alliance not found!';
            },
            $input
        ) ?? $input;

        $input = preg_replace_callback(
            '/\\[player(\\d{0,20})\\]([^\\]]*)\\[\\/player\\d{0,20}\\]/is',
            static function (array $matches) use ($database): string {
                $uid = (int) $matches[2];
                $uname = $database->getUserField($uid, 'username', 0);
                if (!empty($uname) && $uname !== '[?]') {
                    return "<a href=spieler.php?uid={$matches[2]}>{$uname}</a>";
                }
                return 'Player not found!';
            },
            $input
        ) ?? $input;

        $input = preg_replace_callback(
            '/\\[report(\\d{0,20})\\]([^\\]]*)\\[\\/report\\d{0,20}\\]/is',
            static function (array $matches) use ($database): string {
                $reportID = ((int) $matches[1] > 0) ? (int) $matches[1] : (int) $matches[2];
                $report = $database->getNotice2($reportID, null, false);
                if (!empty($report)) {
                    $topicVal = $report['topic'] ?? null;
                    $topic = is_string($topicVal) ? $topicVal : '';
                    return "<a href=berichte.php?id={$reportID}>{$topic}</a>";
                }
                return 'Report not found!';
            },
            $input
        ) ?? $input;

        $input = preg_replace_callback(
            '/\\[coor(\\d{0,20})\\]([^\\]]*)\\[\\/coor\\d{0,20}\\]/is',
            static function (array $matches) use ($database, $generator): string {
                $coordinates = explode('|', $matches[2]);
                $x = $coordinates[0] ?? '';
                $y = $coordinates[1] ?? '';
                $wRef = $database->getVilWref($x, $y);
                $cwref = $generator->getMapCheck($wRef);
                $state = $database->getVillageType($wRef);

                $name = '';
                if ($state > 0) {
                    if ($database->getVillageState($wRef)) {
                        $name = $database->getVillageField($wRef, 'name');
                    } else {
                        $name = defined('ABANDVALLEY') ? (string) constant('ABANDVALLEY') : 'Abandoned Valley';
                    }
                } else {
                    $oasisInfo = $database->getOasisInfo($wRef);
                    $nameVal = $oasisInfo['name'] ?? null;
                    $name = is_string($nameVal) ? $nameVal : '';
                }

                if ($name !== '') {
                    return "<a href=karte.php?d={$wRef}&amp;c={$cwref}>{$name} ({$x}|{$y})</a>";
                }

                return 'Village not found!';
            },
            $input
        ) ?? $input;

        $input = preg_replace('/\\[message\\]/', '', $input) ?? $input;
        $input = preg_replace('/\\[\\/message\\]/', '', $input) ?? $input;

        $patterns = self::patterns();
        $replacements = self::replacements();

        $rendered = preg_replace($patterns, $replacements, $input);
        return is_string($rendered) ? $rendered : $input;
    }

    /**
     * @return list<string>
     */
    private static function patterns(): array
    {
        $patterns = [
            '/\\[b\\](.*?)\\[\\/b\\]/is',
            '/\\[i\\](.*?)\\[\\/i\\]/is',
            '/\\[u\\](.*?)\\[\\/u\\]/is',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $patterns[] = "/\\[tid{$i}\\]/";
        }

        $patterns[] = '/\\[hero\\]/';
        $patterns[] = '/\\[lumber\\]/';
        $patterns[] = '/\\[clay\\]/';
        $patterns[] = '/\\[iron\\]/';
        $patterns[] = '/\\[crop\\]/';

        foreach (array_keys(self::smileyMap()) as $token) {
            $patterns[] = '/' . preg_quote($token, '/') . '/';
        }

        return $patterns;
    }

    /**
     * @return list<string>
     */
    private static function replacements(): array
    {
        $replacements = [
            '<b>$1</b>',
            '<i>$1</i>',
            '<u>$1</u>',
        ];

        for ($i = 1; $i <= 50; $i++) {
            $unitName = self::langConst('U' . $i, 'U' . $i);
            $replacements[] = "<img class='unit u{$i}' src='img/x.gif' title='{$unitName}' alt='{$unitName}'>";
        }

        $heroName = self::langConst('U0', 'Hero');
        $replacements[] = "<img class='unit uhero' src='img/x.gif' title='{$heroName}' alt='{$heroName}'>";

        $lumber = self::langConst('LUMBER', 'Lumber');
        $clay = self::langConst('CLAY', 'Clay');
        $iron = self::langConst('IRON', 'Iron');
        $crop = self::langConst('CROP', 'Crop');
        $replacements[] = "<img src='img/x.gif' class='r1' title='{$lumber}' alt='{$lumber}'>";
        $replacements[] = "<img src='img/x.gif' class='r2' title='{$clay}' alt='{$clay}'>";
        $replacements[] = "<img src='img/x.gif' class='r3' title='{$iron}' alt='{$iron}'>";
        $replacements[] = "<img src='img/x.gif' class='r4' title='{$crop}' alt='{$crop}'>";

        foreach (self::smileyMap() as $token => $class) {
            $alt = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
            $replacements[] = "<img class='smiley {$class}' src='img/x.gif' alt='{$alt}' title='{$alt}'>";
        }

        return $replacements;
    }

    /**
     * @return array<string, string>
     */
    private static function smileyMap(): array
    {
        return [
            '*aha*' => 'aha',
            '*angry*' => 'angry',
            '*cool*' => 'cool',
            '*cry*' => 'cry',
            '*cute*' => 'cute',
            '*depressed*' => 'depressed',
            '*eek*' => 'eek',
            '*ehem*' => 'ehem',
            '*emotional*' => 'emotional',
            ':D' => 'grin',
            ':)' => 'happy',
            '*hit*' => 'hit',
            '*hmm*' => 'hmm',
            '*hmpf*' => 'hmpf',
            '*hrhr*' => 'hrhr',
            '*huh*' => 'huh',
            '*lazy*' => 'lazy',
            '*love*' => 'love',
            '*nocomment*' => 'nocomment',
            '*noemotion*' => 'noemotion',
            '*notamused*' => 'notamused',
            '*pout*' => 'pout',
            '*redface*' => 'redface',
            '*rolleyes*' => 'rolleyes',
            ':(' => 'sad',
            '*shy*' => 'shy',
            '*smile*' => 'smile',
            '*tongue*' => 'tongue',
            '*veryangry*' => 'veryangry',
            '*veryhappy*' => 'veryhappy',
            ';)' => 'wink',
        ];
    }

    private static function langConst(string $name, string $fallback): string
    {
        if (!defined($name)) {
            return $fallback;
        }
        $value = constant($name);
        return is_string($value) ? $value : $fallback;
    }
}
