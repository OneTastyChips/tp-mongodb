<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Predis\Client as PredisClient;

$twig = getTwig();
$manager = getMongoDbManager();

$redis = getRedisClient();

$perPage = 10;
$currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}

$collection = $manager->selectCollection('tp');

$cacheKeyTotal = 'tp:manuscrits:totalDocuments';

$totalDocuments = null;
$cachedTotal = $redis->get($cacheKeyTotal);

if ($cachedTotal !== null) {
    $totalDocuments = (int) $cachedTotal;
} else {
    $totalDocuments = $collection->countDocuments();
    $redis->setex($cacheKeyTotal, 300, $totalDocuments);
}

$totalPages = (int) ceil($totalDocuments / $perPage);
if ($totalPages === 0) {
    $totalPages = 1;
}
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$skip = ($currentPage - 1) * $perPage;


$cacheKeyList = sprintf('tp:manuscrits:page:%d:perPage:%d', $currentPage, $perPage);

$list = null;
$cachedList = $redis->get($cacheKeyList);

if ($cachedList !== null) {
    $list = unserialize($cachedList);
} else {
    $cursor = $collection->find(
        [],
        [
            'limit' => $perPage,
            'skip'  => $skip,
        ]
    );

    $list = $cursor->toArray();

    $redis->setex($cacheKeyList, 300, serialize($list));
}

try {
    echo $twig->render('index.html.twig', [
        'list'        => $list,
        'currentPage' => $currentPage,
        'totalPages'  => $totalPages,
        'perPage'     => $perPage,
        'totalItems'  => $totalDocuments,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
