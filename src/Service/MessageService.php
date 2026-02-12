<?php

declare(strict_types=1);

namespace App\Service;

final class MessageService
{
    public function __construct(
        private readonly \MYSQLi_DB $database,
        private readonly \Session $session,
    ) {
    }

    /**
     * @param array<string, mixed> $post
     */
    public function tryHandlePost(array $post): bool
    {
        $ft = $post['ft'] ?? null;
        if (!\is_string($ft)) {
            return false;
        }

        if (!\in_array($ft, ['m3', 'm4', 'm5'], true)) {
            return false;
        }

        if (isset($post['delmsg'])) {
            $this->deleteMessages($this->collectSelectedIds($post));
            $this->redirectToMessages();
        }

        if (isset($post['archive'])) {
            $this->archiveMessages($this->collectSelectedIds($post));
            $this->redirectToMessages();
        }

        if (isset($post['start'])) {
            $this->unarchiveMessages($this->collectSelectedIds($post));
            $this->redirectToMessages();
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
    private function deleteMessages(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $messageRows = [];
        foreach ($ids as $id) {
            $rows = $this->database->query_new(
                'SELECT id, target, owner FROM ' . TB_PREFIX . 'mdata WHERE id = ? LIMIT 1',
                $id,
            );
            $row = (\is_array($rows) && $rows !== []) ? $rows[0] : null;
            if (!\is_array($row)) {
                continue;
            }

            $messageRows[] = [
                'id' => (int) ($row['id'] ?? $id),
                'target' => (int) ($row['target'] ?? 0),
                'owner' => (int) ($row['owner'] ?? 0),
            ];
        }

        $plan = self::planDeletes($messageRows, (int) $this->session->uid);

        if ($plan['mode5'] !== []) {
            $this->database->getMessage($plan['mode5'], 5);
        }
        if ($plan['mode7'] !== []) {
            $this->database->getMessage($plan['mode7'], 7);
        }
        if ($plan['mode8'] !== []) {
            $this->database->getMessage($plan['mode8'], 8);
        }
    }

    /**
     * @param list<int> $ids
     */
    private function archiveMessages(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $this->database->setArchived($ids);
    }

    /**
     * @param list<int> $ids
     */
    private function unarchiveMessages(array $ids): void
    {
        if ($ids === []) {
            return;
        }
        $this->database->setNorm($ids);
    }

    /**
     * @param list<array{id:int, target:int, owner:int}> $messages
     * @return array{mode5:list<int>, mode7:list<int>, mode8:list<int>}
     */
    public static function planDeletes(array $messages, int $uid): array
    {
        $mode5 = [];
        $mode7 = [];
        $mode8 = [];

        foreach ($messages as $message) {
            $id = (int) $message['id'];
            $target = (int) $message['target'];
            $owner = (int) $message['owner'];

            if ($target === $uid && $owner === $uid) {
                $mode8[] = $id;
                continue;
            }
            if ($target === $uid) {
                $mode5[] = $id;
                continue;
            }
            if ($owner === $uid) {
                $mode7[] = $id;
            }
        }

        return [
            'mode5' => $mode5,
            'mode7' => $mode7,
            'mode8' => $mode8,
        ];
    }

    private function redirectToMessages(): never
    {
        header('Location: nachrichten.php');
        exit;
    }
}
