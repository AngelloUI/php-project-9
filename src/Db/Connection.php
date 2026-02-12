<?php

declare(strict_types=1);

namespace Hexlet\Code\Db;

use PDO;

final class Connection
{
    private static ?PDO $pdo = null;

    private static function getParsedDbUrl(): array
    {
        $dbUrl = parse_url($_ENV['DATABASE_URL']);

        return [
            'driver' => $dbUrl['scheme'],
            'host'   => $dbUrl['host'],
            'port'   => $dbUrl['port'],
            'user'   => $dbUrl['user'],
            'pass'   => $dbUrl['pass'],
            'dbname' => ltrim($dbUrl['path'], '/')
        ];
    }

    public static function getPdo(): PDO
    {
        if (!isset(self::$pdo)) {
            $dbUrl = self::getParsedDbUrl();

            self::$pdo = new PDO("{$dbUrl['driver']}:host={$dbUrl['host']};port={$dbUrl['port']};dbname={$dbUrl['dbname']}", $dbUrl['user'], $dbUrl['pass']);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$pdo;
    }
}
