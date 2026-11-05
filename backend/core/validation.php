<?php
declare(strict_types=1);

/**
 * Strict validation for the website contact / enquiry form.
 * The same limits are sent to the React app (api/site.php → form_rules)
 * so the browser and the server always agree.
 */

function enquiry_rules(): array
{
    $min = max(1, (int) setting('enquiry_min_guests', 10));
    $max = max($min, (int) setting('enquiry_max_guests', 5000));
    return [
        'name'       => ['required' => true, 'min' => 2, 'max' => 60],
        'email'      => ['required' => true, 'max' => 150],
        'phone'      => ['required' => true, 'pattern' => '^(?:\+?91[\s-]?|0)?[6-9]\d{4}[\s-]?\d{5}$', 'hint' => '10-digit Indian mobile number'],
        'event_date' => ['required' => true, 'min_days_ahead' => 1, 'max_days_ahead' => 730],
        'event_type' => ['required' => true, 'options' => enquiry_event_types()],
        'guests'     => ['required' => true, 'min' => $min, 'max' => $max],
        'message'    => ['required' => true, 'min' => 10, 'max' => 1000],
    ];
}

/**
 * Allowed "Event type" values: the list in Admin → Contact Section (one per line),
 * or — when that is empty — the active event titles + Other.
 */
function enquiry_event_types(): array
{
    $lines = preg_split('/\R/', (string) setting('contact_event_types', ''));
    $custom = array_values(array_unique(array_filter(array_map('trim', $lines), 'strlen')));
    if ($custom) {
        return $custom;
    }
    $titles = DB::all('SELECT title FROM events WHERE is_active = 1 ORDER BY sort_order, id');
    return array_merge(array_column($titles, 'title'), ['Other']);
}

/** Normalise whitespace and reject invisible/control characters. */
function clean_text($value, bool $multiline = false): ?string
{
    if (is_array($value) || is_object($value)) {
        return null; // arrays/objects are never valid for a text field
    }
    $s = (string) $value;
    if (!mb_check_encoding($s, 'UTF-8')) {
        return null;
    }
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    // Strip zero-width / bidi control characters
    $s = preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}\x{FEFF}]/u', '', $s);
    // Any remaining control characters (tab/newline allowed only in multiline)
    $ctrl = $multiline ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
    if (preg_match($ctrl, $s)) {
        return null;
    }
    if ($multiline) {
        $s = preg_replace("/[ \t]+/", ' ', $s);
        $s = preg_replace("/\n{3,}/", "\n\n", $s);
    } else {
        $s = preg_replace('/\s+/u', ' ', $s);
    }
    return trim($s);
}

/**
 * @return array{0: array, 1: array<string,string>} [clean data, errors keyed by field]
 */
function validate_enquiry(array $in): array
{
    $rules = enquiry_rules();
    $errors = [];
    $out = [];

    // ---- Full name --------------------------------------------------
    $name = clean_text($in['name'] ?? '');
    $len = $name === null ? 0 : mb_strlen($name);
    if ($name === null) {
        $errors['name'] = 'Name contains invalid characters.';
    } elseif ($name === '') {
        $errors['name'] = 'Please enter your full name.';
    } elseif ($len < $rules['name']['min'] || $len > $rules['name']['max']) {
        $errors['name'] = "Name must be {$rules['name']['min']}–{$rules['name']['max']} characters.";
    } elseif (!preg_match("/^\p{L}[\p{L}\p{M} .'-]*\p{L}\.?$/u", $name)) {
        $errors['name'] = 'Name can contain only letters, spaces, dots, apostrophes and hyphens.';
    } elseif (preg_match("/[ .'-]{2,}/", $name)) {
        $errors['name'] = 'Please enter a valid name.';
    }
    $out['name'] = $name;

    // ---- Email ------------------------------------------------------
    $email = clean_text($in['email'] ?? '');
    $email = $email === null ? null : mb_strtolower($email);
    if ($email === null || $email === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (mb_strlen($email) > $rules['email']['max']
        || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || !preg_match('/^[a-z0-9._%+-]+@[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)*\.[a-z]{2,24}$/', $email)
        || str_contains($email, '..')) {
        $errors['email'] = 'Please enter a valid email address (e.g. name@example.com).';
    }
    $out['email'] = $email;

    // ---- Phone (Indian mobile) --------------------------------------
    $phoneRaw = clean_text($in['phone'] ?? '');
    $digits = $phoneRaw === null ? '' : preg_replace('/\D/', '', $phoneRaw);
    if ($phoneRaw === null || $phoneRaw === '') {
        $errors['phone'] = 'Please enter your phone number.';
    } elseif (!preg_match('/^[0-9+\s()-]+$/', $phoneRaw)) {
        $errors['phone'] = 'Phone number can contain only digits, spaces, + and -.';
    } else {
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && $digits[0] === '0') {
            $digits = substr($digits, 1);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
            $errors['phone'] = 'Please enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';
        } elseif (preg_match('/^(\d)\1{9}$/', $digits)) {
            $errors['phone'] = 'Please enter a real phone number.';
        }
    }
    $out['phone'] = isset($errors['phone']) ? null : '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5);

    // ---- Event date -------------------------------------------------
    $date = clean_text($in['event_date'] ?? '');
    $minDate = (new DateTimeImmutable('today'))->modify('+' . $rules['event_date']['min_days_ahead'] . ' day');
    $maxDate = (new DateTimeImmutable('today'))->modify('+' . $rules['event_date']['max_days_ahead'] . ' day');
    if ($date === null || $date === '') {
        $errors['event_date'] = 'Please choose your event date.';
    } else {
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            $errors['event_date'] = 'Please choose a valid date.';
        } elseif ($d < $minDate) {
            $errors['event_date'] = 'Event date must be from ' . $minDate->format('d M Y') . ' onwards.';
        } elseif ($d > $maxDate) {
            $errors['event_date'] = 'We take bookings up to ' . $maxDate->format('d M Y') . '.';
        }
    }
    $out['event_date'] = $date;

    // ---- Event type -------------------------------------------------
    $type = clean_text($in['event_type'] ?? '');
    if ($type === null || $type === '') {
        $errors['event_type'] = 'Please select the type of event.';
    } elseif (!in_array($type, $rules['event_type']['options'], true)) {
        $errors['event_type'] = 'Please select an event type from the list.';
    }
    $out['event_type'] = $type;

    // ---- Number of guests -------------------------------------------
    $g = $in['guests'] ?? '';
    $g = is_int($g) ? (string) $g : clean_text($g);
    ['min' => $gMin, 'max' => $gMax] = $rules['guests'];
    if ($g === null || $g === '') {
        $errors['guests'] = 'Please enter the number of guests.';
    } elseif (!preg_match('/^[1-9]\d{0,5}$/', $g)) {
        $errors['guests'] = 'Number of guests must be a whole number.';
    } elseif ((int) $g < $gMin || (int) $g > $gMax) {
        $errors['guests'] = "We cater for $gMin to " . number_format($gMax) . ' guests.';
    }
    $out['guests'] = isset($errors['guests']) ? null : (int) $g;

    // ---- Message ----------------------------------------------------
    $msg = clean_text($in['message'] ?? '', true);
    $mLen = $msg === null ? 0 : mb_strlen($msg);
    if ($msg === null) {
        $errors['message'] = 'Message contains invalid characters.';
    } elseif ($msg === '') {
        $errors['message'] = 'Please tell us a little about your event or menu ideas.';
    } elseif ($mLen < $rules['message']['min']) {
        $errors['message'] = "Message must be at least {$rules['message']['min']} characters.";
    } elseif ($mLen > $rules['message']['max']) {
        $errors['message'] = "Message must be under {$rules['message']['max']} characters.";
    } elseif (preg_match('/<\s*\/?\s*[a-z!][^>]*>/i', $msg)) {
        $errors['message'] = 'HTML or code is not allowed in the message.';
    } elseif (preg_match_all('#(https?://|www\.)#i', $msg) > 1) {
        $errors['message'] = 'Please do not include links in your message.';
    } elseif (preg_match('/(.)\1{14,}/u', $msg)) {
        $errors['message'] = 'Please enter a meaningful message.';
    }
    $out['message'] = $msg;

    return [$out, $errors];
}
