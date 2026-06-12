<?php

namespace App\Enums\Traits;

use App\Enums\Traits\ArrayTrait;

trait RenderTrait
{
    use ArrayTrait;
    /**
     * Generate HTML select options
     */
    public static function toSelectOptions(?string $selectedValue = null): string
    {
        $html = '';
        foreach (self::cases() as $case) {
            $selected = $selectedValue == $case->value ? 'selected' : '';
            $html .= sprintf(
                '<option value="%s" %s>%s</option>',
                $case->value,
                $selected,
                $case->label()
            );
        }
        return $html;
    }

    /**
     * Render Select Html Element
     * @param string $name
     * @param ?string $selected
     * @param array<mixed> $attributes
     * @return string
     */
    public static function renderSelect(string $name = 'data_type', ?string $selected = null, array $attributes = []): string
    {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($value));
        }

        $html = sprintf('<select name="%s" id="%s"%s>', htmlspecialchars($name), htmlspecialchars($name), $attrs);

        foreach (self::toArray() as $value => $label) {
            $isSelected = $selected === $value ? ' selected' : '';
            $html .= sprintf('<option value="%s"%s>%s</option>', htmlspecialchars($value), $isSelected, htmlspecialchars($label));
        }

        $html .= '</select>';

        return $html;
    }
}