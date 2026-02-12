<?php

declare(strict_types=1);

namespace Hexlet\Code\Repository;

use Hexlet\Code\Db\Connection;
use PDO;

final class UrlChecksRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getPdo();
    }

    public function save(int $urlId, ?int $statusCode = 0, ?string $h1 = '', ?string $title = '', ?string $description = ''): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO url_checks (url_id, status_code, h1, title, description) VALUES (:urlId, :statusCode, :h1, :title, :description)"
        );
        $stmt->execute([
            'urlId' => $urlId,
            'statusCode' => $statusCode,
            'h1' => $h1,
            'title' => $title,
            'description' => $description,
        ]);
    }

    public function findAllChecksByUrlId(string $urlId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM url_checks WHERE url_id = :urlId ORDER BY created_at DESC"
        );
        $stmt->execute(['urlId' => $urlId]);

        return $stmt->fetchAll();
    }

    public function findLastCheckByUrlId(string $urlId): string
    {
        $stmt = $this->pdo->prepare(
            "SELECT created_at FROM url_checks WHERE url_id = :urlId ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute(['urlId' => $urlId]);

        return $stmt->fetch();
    }
}
