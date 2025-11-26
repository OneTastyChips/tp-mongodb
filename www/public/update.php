<?php

include_once '../init.php';

use MongoDB\BSON\ObjectId;

// Connexion
$manager = getMongoDbManager();
$collection = $manager->selectCollection('tp');

// Vérification du POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = $_POST['id'];
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $century = $_POST['century'] ?? null;

    try {
        $collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => [
                'titre' => $title,
                'auteur' => $author,
                'siecle' => (int) $century
            ]]
        );
        $redis = getRedisClient();
        $redis->del('tp:manuscrits:totalDocuments');
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
        exit;
    }
}

// Retour à la liste
header('Location: /index.php');
exit;
