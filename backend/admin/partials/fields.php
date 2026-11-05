<?php
declare(strict_types=1);

/** Render one form field from its definition in _modules.php. */
function render_field(string $name, array $f, $value, ?string $error = null): string
{
    $type = $f['type'];
    $id = 'f_' . $name;
    $label = e($f['label']) . (!empty($f['required']) ? ' <span class="req">*</span>' : '');
    $help = !empty($f['help']) ? '<small class="help">' . e($f['help']) . '</small>' : '';
    $err = $error ? '<small class="field-error">' . e($error) . '</small>' : '';
    $cls = 'field' . (!empty($f['half']) ? ' half' : '') . ($error ? ' has-error' : '') . ' field-' . $type;
    $ph = (isset($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '')
        . (isset($f['counter']) ? ' data-counter="' . (int) $f['counter'] . '"' : '');
    $req = !empty($f['required']) && $type !== 'image' ? ' required' : '';
    $val = e($value);

    switch ($type) {
        case 'toggle':
            $checked = ((string) $value === '1') ? ' checked' : '';
            return "<div class=\"field full field-toggle\"><label class=\"switch\"><input type=\"checkbox\" name=\"$name\" value=\"1\"$checked><span class=\"slider\"></span><span class=\"switch-label\">" . e($f['label']) . "</span></label></div>";

        case 'textarea':
            $rows = (int) ($f['rows'] ?? 3);
            $input = "<textarea id=\"$id\" name=\"$name\" rows=\"$rows\"$ph$req>$val</textarea>";
            break;

        case 'select':
            $opts = '';
            foreach ($f['options'] as $k => $text) {
                $sel = ((string) $value === (string) $k) ? ' selected' : '';
                $opts .= '<option value="' . e($k) . "\"$sel>" . e($text) . '</option>';
            }
            $input = "<select id=\"$id\" name=\"$name\"$req>$opts</select>";
            break;

        case 'rating':
            $current = (int) ($value ?: 5);
            $stars = '';
            for ($i = 5; $i >= 1; $i--) {
                $chk = $i === $current ? ' checked' : '';
                $stars .= "<input type=\"radio\" id=\"{$id}_$i\" name=\"$name\" value=\"$i\"$chk><label for=\"{$id}_$i\" title=\"$i stars\">★</label>";
            }
            $input = "<div class=\"star-rating\">$stars</div>";
            break;

        case 'icon':
            $opts = '<option value="">— No icon —</option>';
            foreach (ICON_CHOICES as $ic) {
                $sel = $value === $ic ? ' selected' : '';
                $opts .= '<option value="' . e($ic) . "\"$sel>" . e(ucwords(str_replace('-', ' ', $ic))) . '</option>';
            }
            $grid = '';
            foreach (ICON_CHOICES as $ic) {
                $on = $value === $ic ? ' selected' : '';
                $grid .= '<button type="button" class="icon-choice' . $on . '" data-icon="' . e($ic) . '" title="' . e($ic) . '"><i data-lucide="' . e($ic) . '"></i></button>';
            }
            $input = "<div class=\"icon-picker\" data-icon-picker><div class=\"icon-picker-row\"><span class=\"icon-preview\">" . ($value ? '<i data-lucide="' . $val . '"></i>' : '') . "</span><select id=\"$id\" name=\"$name\">$opts</select></div><div class=\"icon-grid\">$grid</div></div>";
            break;

        case 'image':
            $url = media_url($value);
            $preview = $url
                ? '<img src="' . e($url) . '" alt="">'
                : '<span class="drop-hint"><i data-lucide="image-plus"></i>Click or drop an image</span>';
            $remove = $url && empty($f['required'])
                ? "<label class=\"remove-image\"><input type=\"checkbox\" name=\"{$name}__remove\" value=\"1\"> Remove image</label>"
                : '';
            $input = "<div class=\"image-upload\" data-image-upload><label class=\"image-drop\" for=\"$id\">$preview</label>"
                . "<input type=\"file\" id=\"$id\" name=\"$name\" accept=\"image/png,image/jpeg,image/webp,image/gif,image/x-icon\">"
                . "<div class=\"image-meta\"><button type=\"button\" class=\"btn small ghost\" data-pick><i data-lucide=\"upload\"></i> " . ($url ? 'Replace' : 'Upload') . "</button>$remove</div></div>";
            break;

        default:
            $htmlType = in_array($type, ['email', 'url', 'number'], true) ? $type : 'text';
            $minmax = (isset($f['min']) ? ' min="' . (int) $f['min'] . '"' : '') . (isset($f['max']) ? ' max="' . (int) $f['max'] . '"' : '');
            $input = "<input type=\"$htmlType\" id=\"$id\" name=\"$name\" value=\"$val\"$ph$req$minmax>";
    }

    return "<div class=\"$cls\"><label for=\"$id\">$label</label>$input$help$err</div>";
}

function render_fields(array $fields, array $values, array $errors = []): string
{
    $out = '';
    foreach ($fields as $name => $f) {
        $out .= render_field($name, $f, $values[$name] ?? null, $errors[$name] ?? null);
    }
    return $out;
}
