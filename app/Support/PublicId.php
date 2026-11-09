<?php

declare(strict_types=1);

namespace App\Support;

final class PublicId
{
    private const ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    public static function generate(): string
    {
        $time = (int) floor(microtime(true) * 1000);
        $timePart = '';

        for ($index = 0; $index < 10; $index++) {
            $timePart = self::ALPHABET[$time % 32] . $timePart;
            $time = intdiv($time, 32);
        }

        $randomPart = '';

        for ($index = 0; $index < 16; $index++) {
            $randomPart .= self::ALPHABET[random_int(0, 31)];
        }

        return $timePart . $randomPart;
    }
}
