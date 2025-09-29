<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $comment = htmlspecialchars(trim($_POST['comment']));
    $rating = intval($_POST['rating']);
    if ($name && $comment && $rating) {
        $entry = json_encode([
            'name' => $name,
            'comment' => $comment,
            'rating' => $rating,
            'timestamp' => time()
        ]) . PHP_EOL;

        $filePath = '../txt/feedbacks.txt';
        if (file_put_contents($filePath, $entry, FILE_APPEND) === false) {
            die("Failed to write to file: $filePath");
        }

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>