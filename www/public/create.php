<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

$twig = getTwig();
$manager = getMongoDbManager();
$collection = $manager->selectCollection('tp');

if (!empty($_POST)) {
    $title   = $_POST['title'] ?? '';
    $author  = $_POST['author'] ?? '';
    $century = $_POST['century'] ?? '';

    $doc = [
        'titre'     => $title,
        'auteur'    => $author,
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
    ];
    if ($century !== '') {
        $doc['siecle'] = (int) $century;
    }

    $collection->insertOne($doc);

    $redis = getRedisClient();
    $redis->del('tp:manuscrits:totalDocuments');

    header('Location: /index.php');
    exit;
} else {
// render template
    try {
        echo $twig->render('create.html.twig');
    } catch (LoaderError|RuntimeError|SyntaxError $e) {
        echo $e->getMessage();
    }
}

