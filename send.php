<?php
// Bogovich — обработка формы обратной связи
// Данные обрабатываются на сервере оператора (РФ), ФЗ-152

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://bogovich.wine');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Куда отправлять
$to = 'info@bogovich.wine, nekrasov@likesmth.com';

// Получаем данные
$name    = strip_tags(trim($_POST['name'] ?? ''));
$phone   = strip_tags(trim($_POST['phone'] ?? ''));
$email   = strip_tags(trim($_POST['email'] ?? ''));
$guests  = strip_tags(trim($_POST['guests'] ?? ''));
$date    = strip_tags(trim($_POST['date'] ?? ''));
$message = strip_tags(trim($_POST['message'] ?? ''));
$consent = isset($_POST['consent']) ? 'Да' : 'Нет';

// Валидация
if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['error' => 'Заполните обязательные поля']);
    exit;
}

// Защита от спама (honeypot)
if (!empty($_POST['website'])) {
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

// Формируем письмо
$subject = "Новая заявка с сайта bogovich.wine";

$body  = "Новая заявка на визит\n";
$body .= "========================\n\n";
$body .= "Имя: {$name}\n";
$body .= "Телефон: {$phone}\n";
if ($email)   $body .= "Email: {$email}\n";
if ($guests)  $body .= "Гостей: {$guests}\n";
if ($date)    $body .= "Дата: {$date}\n";
if ($message) $body .= "Комментарий: {$message}\n";
$body .= "\nСогласие на обработку ПД: {$consent}\n";
$body .= "Время заявки: " . date('d.m.Y H:i:s') . "\n";
$body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$headers  = "From: noreply@bogovich.wine\r\n";
$headers .= "Reply-To: " . ($email ?: "noreply@bogovich.wine") . "\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";
$headers .= "X-Mailer: Bogovich-Site/1.0\r\n";

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка отправки']);
}
