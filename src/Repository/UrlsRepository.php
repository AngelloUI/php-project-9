<?php

declare(strict_types=1);

namespace Hexlet\Code\Repository;

use Hexlet\Code\Db\Connection;
use PDO;

final class UrlsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getPdo();
    }

    public function save(string $urlName): array
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO urls (name) VALUES (:urlName) RETURNING id, name, created_at"
        );
        $stmt->execute(['urlName' => $urlName]);

        return $stmt->fetch();
    }

    public function findByName(string $urlName)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM urls WHERE name = :name"
        );
        $stmt->execute(['name' => $urlName]);

        return $stmt->fetch();
    }

    public function findById(string $id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM urls WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function findAll(): array
    {
        return $this->pdo->query(
            "SELECT * FROM urls ORDER BY id ASC"
        )->fetchAll();
    }

    public function findAllWithLastCheck(): array
    {
        $query = "SELECT
                    urls.id,
                    urls.name,
                    url_checks.status_code,
                    url_checks.created_at
                FROM urls
                LEFT JOIN url_checks
                    ON url_checks.id = (
                        SELECT id
                        FROM url_checks
                        WHERE url_id = urls.id
                        ORDER BY created_at DESC
                        LIMIT 1
                    )
                ORDER BY urls.id DESC;
                ";

        return $this->pdo->query($query)->fetchAll();
    }
}
