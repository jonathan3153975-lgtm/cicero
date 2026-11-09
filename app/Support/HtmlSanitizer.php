<?php

declare(strict_types=1);

namespace App\Support;

final class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><ul><ol><li><h3><h4><blockquote>';

    private const ALLOWED_TAG_PATTERN = 'p|br|strong|b|em|i|ul|ol|li|h3|h4|blockquote';

    public static function richText(string $html): string
    {
        $clean = strip_tags(trim($html), self::ALLOWED_TAGS);

        $clean = preg_replace(
            '/<\s*(\/?)\s*(' . self::ALLOWED_TAG_PATTERN . ')(?:\s[^>]*)?>/i',
            '<$1$2>',
            $clean
        ) ?? '';

        return trim($clean);
    }
}
