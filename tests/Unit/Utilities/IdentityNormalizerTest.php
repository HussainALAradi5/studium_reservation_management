<?php

declare(strict_types=1);

namespace Tests\Unit\Utilities;

use App\Utilities\Formatting\IdentityNormalizer;
use Tests\TestCase;

final class IdentityNormalizerTest extends TestCase
{
    public function testUsernameAndEmailAreLowerCased(): void
    {
        $this->assertSame('ali.k', IdentityNormalizer::username('  Ali.K '));
        $this->assertSame('ali@studium.test', IdentityNormalizer::email('ALI@Studium.Test'));
    }

    public function testPhoneSeparatorsAreRemoved(): void
    {
        $this->assertSame('+97333000001', IdentityNormalizer::phone('+973 3300-0001'));
        $this->assertSame('+97333000001', IdentityNormalizer::phone('+973 (3300) 0001'));
    }

    public function testCodesAreUpperCased(): void
    {
        $this->assertSame('STD-7KQ2M9XA', IdentityNormalizer::code(' std-7kq2m9xa '));
    }

    public function testUserInputNormalizesOnlyIdentityFields(): void
    {
        $input = IdentityNormalizer::userInput(['username' => 'Ali', 'email' => 'A@B.C', 'phone' => '+973 1234 5678', 'password' => ' Keep Me ']);
        $this->assertSame('ali', $input['username']);
        $this->assertSame('a@b.c', $input['email']);
        $this->assertSame('+97312345678', $input['phone']);
        $this->assertSame(' Keep Me ', $input['password'], 'passwords are never trimmed or lower-cased');
    }

    public function testTextCollapsesWhitespace(): void
    {
        $this->assertSame('Good lights, nice turf', IdentityNormalizer::text("  Good  lights,\n nice   turf "));
    }
}
