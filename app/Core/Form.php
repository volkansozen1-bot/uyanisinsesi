<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Yonetim paneli form alanlari. Her metot hazir HTML dondurur; tum degerler kacirilir.
 *
 *   $f = new Form($values, $errors);
 *   echo $f->text('title', 'Başlık', ['required' => true, 'maxlength' => 200]);
 *   echo $f->richtext('body', 'Metin');
 *   echo $f->image('image', 'Görsel');      // form etiketine enctype="multipart/form-data" ekleyin
 *
 * Ortak secenekler: help (aciklama), required, maxlength, placeholder, class (sarmalayici sinif: "wide" tam satir)
 */
final class Form
{
    /**
     * @param array<string, mixed>  $values
     * @param array<string, string> $errors
     */
    public function __construct(private array $values = [], private array $errors = [])
    {
    }

    public function value(string $name): string
    {
        $v = $this->values[$name] ?? '';
        return is_scalar($v) ? (string) $v : '';
    }

    /** @param array<string, mixed> $o */
    public function text(string $name, string $label, array $o = []): string
    {
        return $this->input($o['type'] ?? 'text', $name, $label, $o);
    }

    /** @param array<string, mixed> $o */
    public function url(string $name, string $label, array $o = []): string
    {
        return $this->input('url', $name, $label, $o + ['placeholder' => 'https://']);
    }

    /** @param array<string, mixed> $o */
    public function number(string $name, string $label, array $o = []): string
    {
        $o += ['step' => '1'];
        return $this->input('number', $name, $label, $o);
    }

    /** Tarih (YYYY-MM-DD) @param array<string, mixed> $o */
    public function date(string $name, string $label, array $o = []): string
    {
        $v = $this->value($name);
        return $this->input('date', $name, $label, $o + ['value' => $v !== '' ? substr($v, 0, 10) : '']);
    }

    /** Tarih + saat @param array<string, mixed> $o */
    public function datetime(string $name, string $label, array $o = []): string
    {
        $v = $this->value($name);
        return $this->input('datetime-local', $name, $label, $o + ['value' => $v !== '' ? str_replace(' ', 'T', substr($v, 0, 16)) : '']);
    }

    /** @param array<string, mixed> $o */
    public function textarea(string $name, string $label, array $o = []): string
    {
        $attrs = $this->attrs($name, $o, ['rows' => $o['rows'] ?? 4]);
        $html  = '<textarea name="' . e($name) . '" id="f-' . e($name) . '"' . $attrs . '>' . e($this->value($name)) . '</textarea>';
        return $this->wrap($name, $label, $html, $o);
    }

    /**
     * Zengin metin editoru (kalin, italik, baslik, liste, baglanti, alinti).
     * JS yoksa duz HTML textarea olarak calisir. Kayitta HtmlSanitizer::clean() ile temizlenmeli.
     * @param array<string, mixed> $o
     */
    public function richtext(string $name, string $label, array $o = []): string
    {
        $html = '<textarea name="' . e($name) . '" id="f-' . e($name) . '" rows="' . (int) ($o['rows'] ?? 12) . '" data-richtext>' . e($this->value($name)) . '</textarea>';
        return $this->wrap($name, $label, $html, $o + ['class' => 'wide']);
    }

    /** @param array<string, mixed> $o */
    public function checkbox(string $name, string $label, array $o = []): string
    {
        $checked = in_array($this->value($name), ['1', 'on', 'true'], true) ? ' checked' : '';
        $html = '<label class="check"><input type="hidden" name="' . e($name) . '" value="0">'
            . '<input type="checkbox" name="' . e($name) . '" value="1"' . $checked . '> <span>' . e($label) . '</span></label>';
        if (!empty($o['help'])) {
            $html .= '<small class="help">' . e((string) $o['help']) . '</small>';
        }
        return '<div class="field' . (isset($o['class']) ? ' ' . e((string) $o['class']) : '') . '">' . $html . '</div>';
    }

    /**
     * @param array<string|int, string> $options deger => etiket
     * @param array<string, mixed> $o  'empty' => 'Seçiniz' gibi bos secenek etiketi
     */
    public function select(string $name, string $label, array $options, array $o = []): string
    {
        $current = $this->value($name);
        $html = '<select name="' . e($name) . '" id="f-' . e($name) . '"' . $this->attrs($name, $o) . '>';
        if (array_key_exists('empty', $o)) {
            $html .= '<option value="">' . e((string) $o['empty']) . '</option>';
        }
        foreach ($options as $value => $text) {
            $sel = (string) $value === $current ? ' selected' : '';
            $html .= '<option value="' . e((string) $value) . '"' . $sel . '>' . e($text) . '</option>';
        }
        $html .= '</select>';
        return $this->wrap($name, $label, $html, $o);
    }

    /**
     * Gorsel secme alani: mevcut gorselin onizlemesi + "kaldir" kutusu + dosya secici.
     * Kayitta AdminController::imageField($name, $mevcutYol) ile islenir.
     * @param array<string, mixed> $o
     */
    public function image(string $name, string $label, array $o = []): string
    {
        $current = $this->value($name);
        $html = '<div class="image-field" data-image-field>';
        $html .= '<div class="image-preview">';
        if ($current !== '') {
            $html .= '<img src="' . e(url($current)) . '" alt="">';
        } else {
            $html .= '<span class="muted">Görsel yok</span>';
        }
        $html .= '</div><div class="image-controls">';
        $html .= '<input type="file" name="' . e($name) . '" id="f-' . e($name) . '" accept="image/jpeg,image/png,image/webp,image/gif">';
        if ($current !== '') {
            $html .= '<label class="check"><input type="checkbox" name="' . e($name) . '_remove" value="1"> <span>Görseli kaldır</span></label>';
        }
        $html .= '<small class="help">' . e((string) ($o['help'] ?? 'JPG, PNG, WebP veya GIF; en fazla 8 MB. Büyük görseller otomatik küçültülür.')) . '</small>';
        $html .= '</div></div>';
        unset($o['help']);
        return $this->wrap($name, $label, $html, $o + ['class' => 'wide'], false);
    }

    /** @param array<string, mixed> $o */
    private function input(string $type, string $name, string $label, array $o): string
    {
        $value = array_key_exists('value', $o) ? (string) $o['value'] : $this->value($name);
        $html  = '<input type="' . e($type) . '" name="' . e($name) . '" id="f-' . e($name) . '" value="' . e($value) . '"' . $this->attrs($name, $o) . '>';
        return $this->wrap($name, $label, $html, $o);
    }

    /**
     * @param array<string, mixed> $o
     * @param array<string, mixed> $extra
     */
    private function attrs(string $name, array $o, array $extra = []): string
    {
        $out = '';
        foreach (['maxlength', 'placeholder', 'min', 'max', 'step', 'rows', 'pattern', 'autocomplete'] as $key) {
            $val = $o[$key] ?? $extra[$key] ?? null;
            if ($val !== null && $val !== '') {
                $out .= ' ' . $key . '="' . e((string) $val) . '"';
            }
        }
        if (!empty($o['required'])) {
            $out .= ' required';
        }
        if (!empty($o['slug_from'])) {
            $out .= ' data-slug-from="f-' . e((string) $o['slug_from']) . '"';
        }
        if (isset($this->errors[$name])) {
            $out .= ' aria-invalid="true" aria-describedby="e-' . e($name) . '"';
        }
        return $out;
    }

    /** @param array<string, mixed> $o */
    private function wrap(string $name, string $label, string $control, array $o, bool $useLabel = true): string
    {
        $class = 'field' . (isset($o['class']) ? ' ' . e((string) $o['class']) : '');
        $req   = !empty($o['required']) ? ' <span class="req" aria-hidden="true">*</span>' : '';
        $html  = '<div class="' . $class . '">';
        $html .= $useLabel
            ? '<label for="f-' . e($name) . '">' . e($label) . $req . '</label>'
            : '<span class="field-label">' . e($label) . $req . '</span>';
        $html .= $control;
        if (isset($this->errors[$name])) {
            $html .= '<span class="field-error" id="e-' . e($name) . '">' . e($this->errors[$name]) . '</span>';
        } elseif (!empty($o['help'])) {
            $html .= '<small class="help">' . e((string) $o['help']) . '</small>';
        }
        return $html . '</div>';
    }
}
