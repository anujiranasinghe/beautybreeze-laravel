<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$uri = getenv('MONGODB_URI');
echo "URI=", $uri, "\n";
$client = new MongoDB\Client($uri);
foreach ($client->listDatabases() as $db) {
    echo $db->getName(), "\n";
}
