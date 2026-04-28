<?php
// models/Post.php

namespace App\Models;

class Post
{
    // ── Fetch methods ─────────────────────────────────────

    public static function find(int $id): ?array
    {
        $stmt = get_db()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    a.username AS author_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN admin_users a ON a.id = p.author_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = get_db()->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug,
                    a.username AS author_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN admin_users a ON a.id = p.author_id
             WHERE p.slug = ? AND p.status = "published"
             LIMIT 1'
        );
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public static function all(array $options = []): array
    {
        $status   = $options['status']      ?? null;
        $category = (int)($options['category'] ?? 0);
        $search   = $options['search']      ?? '';
        $limit    = (int)($options['limit'] ?? 20);
        $offset   = (int)($options['offset'] ?? 0);
        $orderBy  = $options['order_by']    ?? 'p.published_at DESC';

        $where  = ['1=1'];
        $params = [];

        if ($status)   { $where[] = 'p.status = ?';       $params[] = $status; }
        if ($category) { $where[] = 'p.category_id = ?';  $params[] = $category; }
        if ($search)   { $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
                FROM posts p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE ' . implode(' AND ', $where) . "
                ORDER BY $orderBy
                LIMIT $limit OFFSET $offset";

        $stmt = get_db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function count(array $options = []): int
    {
        $status   = $options['status']      ?? null;
        $category = (int)($options['category'] ?? 0);
        $search   = $options['search']      ?? '';

        $where  = ['1=1'];
        $params = [];

        if ($status)   { $where[] = 'p.status = ?';       $params[] = $status; }
        if ($category) { $where[] = 'p.category_id = ?';  $params[] = $category; }
        if ($search)   { $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

        $stmt = get_db()->prepare('SELECT COUNT(*) FROM posts p WHERE ' . implode(' AND ', $where));
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function latest(int $limit = 5): array
    {
        return self::all(['status' => 'published', 'limit' => $limit]);
    }

    public static function featured(int $limit = 5): array
    {
        $stmt = get_db()->prepare(
            'SELECT p.*, c.name AS category_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.is_featured = 1 AND p.status = "published"
             ORDER BY p.published_at DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public static function related(int $postId, int $categoryId, int $limit = 3): array
    {
        $stmt = get_db()->prepare(
            'SELECT p.*, c.name AS category_name
             FROM posts p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.category_id = ? AND p.id != ? AND p.status = "published"
             ORDER BY p.published_at DESC
             LIMIT ?'
        );
        $stmt->execute([$categoryId, $postId, $limit]);
        return $stmt->fetchAll();
    }

    // ── Write methods ──────────────────────────────────────

    public static function create(array $data): int
    {
        $stmt = get_db()->prepare(
            'INSERT INTO posts (title, slug, excerpt, content, thumbnail, category_id, author_id,
                                status, is_featured, meta_title, meta_description, meta_keywords, published_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt']          ?? null,
            $data['content'],
            $data['thumbnail'],
            $data['category_id']      ?? null,
            $data['author_id']        ?? null,
            $data['status']           ?? 'draft',
            $data['is_featured']      ?? 0,
            $data['meta_title']       ?? null,
            $data['meta_description'] ?? null,
            $data['meta_keywords']    ?? null,
            ($data['status'] ?? 'draft') === 'published' ? date('Y-m-d H:i:s') : null,
        ]);
        return (int)get_db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $existing = self::find($id);
        $publishedAt = $existing['published_at'];
        if (($data['status'] ?? '') === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $stmt = get_db()->prepare(
            'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, thumbnail=?, category_id=?,
             status=?, is_featured=?, meta_title=?, meta_description=?, meta_keywords=?, published_at=?, updated_at=NOW()
             WHERE id=?'
        );
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt']          ?? null,
            $data['content'],
            $data['thumbnail'],
            $data['category_id']      ?? null,
            $data['status']           ?? 'draft',
            $data['is_featured']      ?? 0,
            $data['meta_title']       ?? null,
            $data['meta_description'] ?? null,
            $data['meta_keywords']    ?? null,
            $publishedAt,
            $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $post = self::find($id);
        if ($post && $post['thumbnail']) {
            $path = ROOT_PATH . '/' . $post['thumbnail'];
            if (file_exists($path)) @unlink($path);
        }
        return get_db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    }

    public static function incrementViews(int $id): void
    {
        try {
            get_db()->prepare('UPDATE posts SET views = views + 1 WHERE id = ?')->execute([$id]);
        } catch (\Throwable) {}
    }

    /** Ensure slug is unique, append -N if needed */
    public static function uniqueSlug(string $base, ?int $excludeId = null): string
    {
        $slug = $base;
        $i    = 1;
        while (true) {
            $sql    = 'SELECT id FROM posts WHERE slug = ?' . ($excludeId ? ' AND id != ?' : '');
            $params = $excludeId ? [$slug, $excludeId] : [$slug];
            $stmt   = get_db()->prepare($sql);
            $stmt->execute($params);
            if (!$stmt->fetch()) break;
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    /** Count featured published posts */
    public static function featuredCount(): int
    {
        return (int)get_db()->query('SELECT COUNT(*) FROM posts WHERE is_featured = 1 AND status = "published"')->fetchColumn();
    }
}
