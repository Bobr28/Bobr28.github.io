<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Файл для хранения отзывов
$reviewsFile = 'reviews.json';

// Проверяем метод запроса
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Получаем отзывы
    if (file_exists($reviewsFile)) {
        $reviewsData = file_get_contents($reviewsFile);
        $reviews = json_decode($reviewsData, true) ?: [];
    } else {
        $reviews = [];
    }
    
    echo json_encode($reviews);
    
} elseif ($method === 'POST') {
    // Добавляем новый отзыв
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }
    
    // Валидация данных
    if (empty($input['name']) || empty($input['text']) || !isset($input['rating'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    // Читаем существующие отзывы
    $reviews = [];
    if (file_exists($reviewsFile)) {
        $reviewsData = file_get_contents($reviewsFile);
        $reviews = json_decode($reviewsData, true) ?: [];
    }
    
    // Добавляем новый отзыв
    $newReview = [
        'id' => time() . rand(1000, 9999), // Генерируем уникальный ID
        'name' => htmlspecialchars(trim($input['name'])),
        'rating' => (int) $input['rating'],
        'text' => htmlspecialchars(trim($input['text'])),
        'date' => date('Y-m-d\TH:i:s\Z')
    ];
    
    array_unshift($reviews, $newReview);
    
    // Сохраняем в файл
    if (file_put_contents($reviewsFile, json_encode($reviews, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        echo json_encode(['success' => true, 'review' => $newReview]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save review']);
    }
    
} elseif ($method === 'OPTIONS') {
    // Для CORS preflight запросов
    http_response_code(200);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>