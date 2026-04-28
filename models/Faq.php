<?php
// models/Faq.php

namespace App\Models;

class Faq
{
    public static function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? 'WHERE is_active = 1' : '';
        return get_db()->query("SELECT * FROM faqs $where ORDER BY sort_order ASC, id ASC")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = get_db()->prepare('SELECT * FROM faqs WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        get_db()->prepare('INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?,?,?,?)')
            ->execute([$data['question'], $data['answer'], $data['sort_order'] ?? 0, $data['is_active'] ?? 1]);
        return (int)get_db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        return get_db()->prepare('UPDATE faqs SET question=?, answer=?, sort_order=?, is_active=? WHERE id=?')
            ->execute([$data['question'], $data['answer'], $data['sort_order'] ?? 0, $data['is_active'] ?? 1, $id]);
    }

    public static function delete(int $id): bool
    {
        return get_db()->prepare('DELETE FROM faqs WHERE id = ?')->execute([$id]);
    }
}
