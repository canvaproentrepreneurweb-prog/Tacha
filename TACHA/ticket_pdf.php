<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/lib/phpqrcode/qrlib.php';
require_once __DIR__ . '/lib/fpdf/fpdf.php';

$token = trim($_GET['t'] ?? '');
if ($token === '') {
    http_response_code(400);
    echo 'Token manquant.';
    exit;
}

$stmt = db()->prepare(
    'SELECT t.*, e.title, e.city, e.venue, e.event_date, e.event_time, e.ticket_template_path, e.qr_x, e.qr_y, e.qr_size,
            u.name AS participant
     FROM tickets t
     JOIN events e ON e.id = t.event_id
     LEFT JOIN users u ON u.id = t.user_id
     WHERE t.token = ?
     LIMIT 1'
);
$stmt->execute([$token]);
$ticket = $stmt->fetch();

if (!$ticket) {
    http_response_code(404);
    echo 'Ticket introuvable.';
    exit;
}

$templatePath = __DIR__ . '/' . ltrim((string) ($ticket['ticket_template_path'] ?? ''), '/');
if (!is_file($templatePath)) {
    $templatePath = __DIR__ . '/' . image_path('Baniere_accuil.png');
}

$qrPng = __DIR__ . '/generated/qr/' . $ticket['token'] . '.png';
if (!is_file($qrPng)) {
    QRcode::png($ticket['token'], $qrPng, 'L', 6, 2);
}

$baseData = file_get_contents($templatePath);
$baseImg = $baseData ? imagecreatefromstring($baseData) : null;
$qrImg = imagecreatefrompng($qrPng);

if (!$baseImg || !$qrImg) {
    http_response_code(500);
    echo 'Impossible de generer le ticket.';
    exit;
}

$w = imagesx($baseImg);
$h = imagesy($baseImg);
$defaultSize = max(120, (int) round(min($w, $h) * 0.22));
$qrSize = (int) ($ticket['qr_size'] ?? $defaultSize);
if ($qrSize <= 0) {
    $qrSize = $defaultSize;
}

$qrX = (int) ($ticket['qr_x'] ?? 0);
$qrY = (int) ($ticket['qr_y'] ?? 0);
if ($qrX <= 0 || $qrY <= 0) {
    $qrX = $w - $qrSize - 35;
    $qrY = $h - $qrSize - 35;
}

imagecopyresampled($baseImg, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));

$txtColor = imagecolorallocate($baseImg, 255, 255, 255);
$shadow = imagecolorallocate($baseImg, 10, 20, 40);
$name = ticket_holder_name($ticket);
$line1 = $ticket['title'];
$line2 = $name . ' | ' . date('d/m/Y', strtotime($ticket['event_date'])) . ' ' . substr($ticket['event_time'], 0, 5);
$line3 = 'TOKEN: ' . $ticket['token'];

imagestring($baseImg, 5, 32, 30, $line1, $shadow);
imagestring($baseImg, 5, 31, 29, $line1, $txtColor);
imagestring($baseImg, 4, 32, 55, $line2, $txtColor);
imagestring($baseImg, 5, 32, 78, $line3, $txtColor);

$tmpJpg = __DIR__ . '/generated/tmp/' . $ticket['token'] . '.jpg';
imagejpeg($baseImg, $tmpJpg, 92);
imagedestroy($qrImg);
imagedestroy($baseImg);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image($tmpJpg, 10, 10, 190);
$pdf->Output('I', 'ticket-' . $ticket['token'] . '.pdf');
?>

