<?php
date_default_timezone_set('Asia/Manila');

$filePath = '../txt/feedbacks.txt';
$feedbacks = [];

if (file_exists($filePath)) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $feedback = json_decode($line, true);
        if ($feedback) {
            $feedbacks[] = $feedback;
        }
    }
}

$startDate = isset($_GET['start_date']) ? strtotime($_GET['start_date'] . ' 00:00:00') : null;
$endDate = isset($_GET['end_date']) ? strtotime($_GET['end_date'] . ' 23:59:59') : null;

$filteredFeedbacks = array_filter($feedbacks, function ($feedback) use ($startDate, $endDate) {
    $timestamp = $feedback['timestamp'];
    if ($startDate && $timestamp < $startDate) {
        return false;
    }
    if ($endDate && $timestamp > $endDate) {
        return false;
    }
    return true;
});

if (!empty($filteredFeedbacks)) {
    foreach ($filteredFeedbacks as $fb) {
        echo '<div class="feedback-item">';
        echo '<div class="rating">';
        for ($i = 1; $i <= 5; $i++) {
            echo '<span style="color: ' . ($i <= $fb['rating'] ? 'orange' : '#ccc') . ';">★</span>';
        }
        echo '</div>';
        echo '<strong class="feedback-name">' . htmlspecialchars($fb['name']) . '</strong><br/>';
        echo '<p class="feedback-comment">' . htmlspecialchars($fb['comment']) . '</p>';
        echo '<small class="feedback-timestamp">' . date('Y-m-d H:i', $fb['timestamp']) . '</small>';
        echo '</div>';
    }
} else {
    echo '<p>No feedbacks available.</p>';
}
?>