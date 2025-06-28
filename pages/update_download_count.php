<?php
session_start();
include_once 'includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if (!isset($_POST['resource_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de ressource manquant']);
    exit;
}

$resource_id = (int)$_POST['resource_id'];

// Mettre à jour le compteur
$stmt = $conn->prepare("UPDATE ressource SET telechargements = COALESCE(telechargements, 0) + 1 WHERE id_ressource = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête']);
    exit;
}

if (!$stmt->bind_param("i", $resource_id)) {
    echo json_encode(['success' => false, 'message' => 'Erreur de liaison des paramètres']);
    exit;
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution de la requête']);
    exit;
}

// Récupérer le nouveau compteur
$stmt = $conn->prepare("SELECT telechargements FROM ressource WHERE id_ressource = ?");
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();
$resource = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => $resource['telechargements']
]); 