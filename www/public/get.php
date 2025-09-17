<?php

include_once '../init.php';

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use MongoDB\BSON\ObjectId;

$twig = getTwig();
$manager = getMongoDbManager();

// Vérifier qu’on a bien reçu un id
$id = $_GET['id'] ?? null;
$entity = null;

if ($id) {
    try {
        $collection = $manager->selectCollection('tp');
        $entity = $collection->findOne(['_id' => new ObjectId($id)]);
    } catch (Exception $e) {
        $entity = ['error' => $e->getMessage()];
    }
}

// render template
try {
    echo $twig->render('get.html.twig', ['entity' => $entity]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}
