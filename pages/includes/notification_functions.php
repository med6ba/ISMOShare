<?php
// Fonctions de gestion des notifications
function addNotification($conn, $id_utilisateur, $type_notification, $id_source, $message) {
    $stmt = $conn->prepare("INSERT INTO notification (id_utilisateur, type_notification, id_source, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isis", $id_utilisateur, $type_notification, $id_source, $message);
    return $stmt->execute();
}

function getUnreadNotificationsCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notification WHERE id_utilisateur = ? AND est_lu = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['count'];
}

function markNotificationAsRead($conn, $notification_id) {
    $stmt = $conn->prepare("UPDATE notification SET est_lu = 1 WHERE id_notification = ?");
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}
?> 