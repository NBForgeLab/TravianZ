<?php

declare(strict_types=1);

namespace App\Service;

final class ReportService
{
    public function __construct(
        private readonly \MYSQLi_DB $database,
        private readonly \Session $session,
    ) {
    }

    /**
     * @param array<string, mixed> $get
     */
    public function initializeLegacyMessage(\Message $legacyMessage, array $get): void
    {
        $t = $get['t'] ?? null;
        if ($t !== null && \is_scalar($t)) {
            $tInt = (int) $t;
            $hasPlus = (bool) ($this->session->plus ?? false);
            if ($tInt === 5 && !$hasPlus) {
                header('Location: berichte.php');
                exit;
            }

            $noticeTypes = self::resolveNoticeTypesForTab($tInt, $hasPlus);
            if ($noticeTypes !== null) {
                $legacyMessage->noticearray = self::filterNoticesByType(
                    $this->database->getNotice((int) $this->session->uid),
                    $noticeTypes,
                );
            }
        }

        $id = $get['id'] ?? null;
        if ($id !== null && \is_scalar($id)) {
            $legacyMessage->readingNotice = $this->getReadNotice((int) $id) ?? [];
        }
    }

    /**
     * @param array<string, mixed> $post
     */
    public function tryHandlePost(array $post): bool
    {
        if (isset($post['del_x'])) {
            $this->deleteNotices($this->collectSelectedIds($post));
            $this->redirectToReports();
        }
        if (isset($post['archive_x'])) {
            $this->archiveNotices($this->collectSelectedIds($post));
            $this->redirectToReports();
        }
        if (isset($post['start_x'])) {
            $this->unarchiveNotices($this->collectSelectedIds($post));
            $this->redirectToReports();
        }

        return false;
    }

    /**
     * @param array<string, mixed> $post
     * @return list<int>
     */
    private function collectSelectedIds(array $post): array
    {
        $ids = [];
        for ($i = 1; $i <= 10; $i++) {
            $key = 'n' . $i;
            if (!isset($post[$key]) || !\is_scalar($post[$key])) {
                continue;
            }

            $value = (string) $post[$key];
            if ($value === '' || !\ctype_digit($value)) {
                continue;
            }

            $ids[] = (int) $value;
        }

        return $ids;
    }

    /**
     * @param list<int> $ids
     */
    private function deleteNotices(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $this->database->removeNotice($ids);
    }

    /**
     * @param list<int> $ids
     */
    private function archiveNotices(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $this->database->archiveNotice($ids);
    }

    /**
     * @param list<int> $ids
     */
    private function unarchiveNotices(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $this->database->unarchiveNotice($ids);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getReadNotice(int $id): ?array
    {
        $notice = $this->database->getNotice2($id);
        if (!\is_array($notice)) {
            return null;
        }

        $uid = (int) ($notice['uid'] ?? 0);
        $ally = (int) ($notice['ally'] ?? 0);

        $sessionUid = (int) ($this->session->uid ?? 0);
        $sessionAlliance = (int) ($this->session->alliance ?? 0);

        if ($uid !== $sessionUid && $ally !== $sessionAlliance) {
            return null;
        }

        if ($uid === $sessionUid) {
            $this->database->noticeViewed((int) ($notice['id'] ?? $id));
        }

        return $notice;
    }

    /**
     * @return list<int>|null
     */
    public static function resolveNoticeTypesForTab(int $t, bool $hasPlus): ?array
    {
        return match ($t) {
            1 => [8, 15, 16, 17],
            2 => [10, 11, 12, 13],
            3 => [1, 2, 3, 4, 5, 6, 7],
            4 => [0, 18, 19, 20, 21],
            5 => $hasPlus ? [9] : null,
            default => null,
        };
    }

    /**
     * @param array<int, array<string, mixed>> $notices
     * @param list<int> $types
     * @return array<int, array<string, mixed>>
     */
    public static function filterNoticesByType(array $notices, array $types): array
    {
        if ($notices === []) {
            return [];
        }

        $out = [];
        foreach ($notices as $notice) {
            $ntype = (int) ($notice['ntype'] ?? -1);
            if (\in_array($ntype, $types, true)) {
                $out[] = $notice;
            }
        }

        return $out;
    }

    public static function mapReportType(int $type): int
    {
        return match ($type) {
            2, 4, 5, 6, 7, 18, 20, 21 => 1,
            11, 12, 13, 14 => 10,
            16, 17 => 15,
            19 => 3,
            23 => 22,
            default => $type,
        };
    }

    private function redirectToReports(): never
    {
        header('Location: berichte.php');
        exit;
    }
}
