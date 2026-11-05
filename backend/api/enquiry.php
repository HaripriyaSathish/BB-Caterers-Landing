<?php
/**
 * POST /api/enquiry.php   (JSON or form-data)
 * Fields: name, email, phone, event_date (YYYY-MM-DD), event_type, guests, message, website (honeypot)
 */
declare(strict_types=1);

require __DIR__ . '/_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

require __DIR__ . '/../core/validation.php';

// Reject oversized bodies outright (the form is a few hundred bytes).
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 20000) {
    json_response(['success' => false, 'message' => 'Request is too large.'], 413);
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (str_contains($contentType, 'application/json')) {
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        json_response(['success' => false, 'message' => 'Invalid request format.'], 400);
    }
} elseif (str_contains($contentType, 'application/x-www-form-urlencoded') || str_contains($contentType, 'multipart/form-data')) {
    $input = $_POST;
} else {
    json_response(['success' => false, 'message' => 'Unsupported request format.'], 415);
}

// Honeypot — bots fill hidden fields; pretend success.
if (trim((string) (is_scalar($input['website'] ?? '') ? ($input['website'] ?? '') : 'x')) !== '') {
    json_response(['success' => true, 'message' => setting('contact_success_message', 'Thank you!')]);
}

[$data, $errors] = validate_enquiry($input);
if ($errors) {
    json_response([
        'success' => false,
        'message' => count($errors) === 1 ? reset($errors) : 'Please correct the highlighted fields.',
        'errors'  => $errors,
    ], 422);
}

// Rate limit: max 5 enquiries per IP per hour.
$ip = client_ip();
$recent = (int) DB::value('SELECT COUNT(*) FROM enquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)', [$ip]);
if ($recent >= 5) {
    json_response(['success' => false, 'message' => 'Too many enquiries from your network. Please call us instead.'], 429);
}

// Duplicate guard: same email + event date in the last 24 hours.
$dup = DB::value('SELECT id FROM enquiries WHERE email = ? AND event_date = ? AND created_at > (NOW() - INTERVAL 1 DAY) LIMIT 1', [$data['email'], $data['event_date']]);
if ($dup) {
    json_response(['success' => false, 'message' => 'We already have your enquiry for this date. Our team will contact you soon.', 'errors' => ['email' => 'An enquiry for this date was already sent from this email.']], 409);
}

$id = DB::insert('enquiries', [
    'name'       => $data['name'],
    'email'      => $data['email'],
    'phone'      => $data['phone'],
    'event_date' => $data['event_date'],
    'event_type' => $data['event_type'],
    'guests'     => $data['guests'],
    'message'    => $data['message'],
    'ip'         => $ip,
]);

// Emails — failures are logged but never block the visitor.
if (Mailer::isConfigured()) {
    $rows = '';
    foreach (['Name' => $data['name'], 'Email' => $data['email'], 'Phone' => $data['phone'], 'Event date' => $data['event_date'],
              'Event type' => $data['event_type'], 'Guests' => $data['guests'], 'Message' => nl2br(e($data['message']))] as $label => $val) {
        $val = $label === 'Message' ? $val : e($val);
        $rows .= '<tr><td style="padding:6px 10px;color:#8a7a5a;width:120px;vertical-align:top">' . $label . '</td><td style="padding:6px 10px">' . ($val !== '' ? $val : '—') . '</td></tr>';
    }
    $notify = setting('smtp_notify_email') ?: setting('contact_email');
    try {
        if ($notify) {
            (new Mailer())->send(
                $notify,
                'New catering enquiry #' . $id . ' — ' . $data['name'],
                email_template('New catering enquiry', '<table cellpadding="0" cellspacing="0" style="font-size:14px;width:100%">' . $rows . '</table>'),
                $data['email'],
                $data['name']
            );
        }
        if (setting('smtp_autoreply') === '1') {
            (new Mailer())->send(
                $data['email'],
                'We received your enquiry — ' . setting('site_name', 'BB Caterers'),
                email_template('Thank you, ' . e($data['name']) . '!',
                    '<p style="font-size:14px;line-height:1.6">' . e(setting('contact_success_message')) . '</p>'
                    . '<p style="font-size:14px;line-height:1.6">Phone: ' . e(setting('contact_phone')) . '<br>Email: ' . e(setting('contact_email')) . '</p>')
            );
        }
    } catch (Throwable $e) {
        error_log('[enquiry mail] ' . $e->getMessage());
    }
}

json_response(['success' => true, 'message' => setting('contact_success_message', 'Thank you! We will get back to you shortly.'), 'id' => $id], 201);
