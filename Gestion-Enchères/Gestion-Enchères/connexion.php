<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=plateforme_encheres;charset=utf8",
    "root",
    "houssam123"
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);