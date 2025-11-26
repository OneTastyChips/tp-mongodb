<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';

use MongoDB\Database;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// env configuration
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

function getTwig(): Environment
{
    // twig configuration
    return new Environment(new FilesystemLoader('../templates'));
}

function getMongoDbManager(): Database
{
    $client = new MongoDB\Client("mongodb://{$_ENV['MDB_USER']}:{$_ENV['MDB_PASS']}@{$_ENV['MDB_SRV']}:{$_ENV['MDB_PORT']}");
    return $client->selectDatabase($_ENV['MDB_DB']);
}



function getRedisClient(): ?PredisClient
{
    $redisEnable = getenv("REDIS_ENABLE") === "true";

    if (!$redisEnable) {
        return null;
    }

    $host = getenv("REDIS_HOST") ?: "127.0.0.1";
    $port = getenv("REDIS_PORT") ?: 6379;
    $password = getenv("REDIS_PASSWORD") ?: null;

    $params = [
        'scheme' => 'tcp',
        'host'   => $host,
        'port'   => $port,
    ];

    if ($password) {
        $params['password'] = $password;
    }

    try {
        return new PredisClient($params);
    } catch (Exception $e) {
        error_log("Erreur Redis : " . $e->getMessage());
        return null;
    }
}