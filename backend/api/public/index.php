<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$url = $_GET['url'] ?? $_SERVER['REQUEST_URI'];

require_once __DIR__.'/../routes/index.php';
