<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validators\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testItAcceptsValidInput(): void
    {
        $validator = (new Validator([
            'email' => 'admin@local.test',
            'password' => 'admin123',
        ]))
            ->required('email')
            ->email('email')
            ->required('password')
            ->minLength('password', 6);

        self::assertTrue($validator->passes());
        self::assertSame([], $validator->errors());
    }

    public function testItRejectsInvalidInput(): void
    {
        $validator = (new Validator([
            'email' => 'invalido',
            'password' => '123',
        ]))
            ->required('email')
            ->email('email')
            ->minLength('password', 6);

        self::assertTrue($validator->fails());
        self::assertArrayHasKey('email', $validator->errors());
        self::assertArrayHasKey('password', $validator->errors());
    }

    public function testItTreatsBlankStringsAsEmpty(): void
    {
        $validator = (new Validator(['email' => '   ']))
            ->required('email');

        self::assertTrue($validator->fails());
    }
}
