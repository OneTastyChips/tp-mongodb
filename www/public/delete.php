<?php

include_once '../init.php';

use MongoDB\BSON\ObjectId;

$manager = getMongoDbManager();
$collection = $manager->selectCollection('tp');

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $collection->deleteOne(['_id' => new ObjectId($id)]);
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
}

header('Location: /index.php');
exit;
