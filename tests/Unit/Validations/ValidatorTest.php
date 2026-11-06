<?php

declare(strict_types=1);

namespace Tests\Unit\Validations;

use App\Constants\Common\RegexPatterns;
use App\Dtos\Reviews\ReviewFormDto;
use App\Dtos\Users\RegisterDto;
use App\Enums\Users\UserRole;
use App\Exceptions\Common\ValidationException;
use App\Validations\Common\Validator;
use Tests\TestCase;

final class ValidatorTest extends TestCase
{
    public function testCollectsEveryFieldError(): void
    {
        $error = $this->assertThrows(ValidationException::class, fn () => (new Validator(['email' => 'nope', 'age' => '200']))
            ->email('email')
            ->integer('age', 'Age', 1, 120)
            ->string('name', 'Name', 2, 10)
            ->validated());
        $this->assertSame(['email', 'age', 'name'], array_keys($error->details['fields']));
    }

    public function testCleansTypes(): void
    {
        $data = (new Validator(['n' => '5', 'd' => '2.500', 'role' => 'manager', 'on' => 'on', 't' => '08:30']))
            ->integer('n', 'N', 1, 9)
            ->decimal('d', 'D', 0, 10)
            ->enum('role', 'role', UserRole::class)
            ->boolean('on')
            ->time('t', 'T')
            ->validated();
        $this->assertSame(5, $data['n']);
        $this->assertSame(2.5, $data['d']);
        $this->assertSame(UserRole::MANAGER, $data['role']);
        $this->assertTrue($data['on']);
        $this->assertSame('08:30', $data['t']);
    }

    public function testRejectsImpossibleDate(): void
    {
        $this->assertThrows(ValidationException::class, fn () => (new Validator(['d' => '2030-02-30']))->date('d', 'Date')->validated());
    }

    public function testRegisterRejectsWeakPasswordAndMismatch(): void
    {
        $error = $this->assertThrows(ValidationException::class, fn () => RegisterDto::fromInput([
            'fullName' => 'Ali Hassan', 'username' => 'ali', 'email' => 'a@b.test', 'phone' => '+97333000001',
            'password' => 'lettersonly', 'passwordConfirmation' => 'different',
        ]));
        $this->assertTrue(isset($error->details['fields']['password']));
        $this->assertTrue(isset($error->details['fields']['passwordConfirmation']));
    }

    public function testReviewRatingMustBeOneToFive(): void
    {
        $this->assertThrows(ValidationException::class, fn () => ReviewFormDto::fromInput(['rating' => '6', 'comment' => 'Nice']));
        $this->assertSame(5, ReviewFormDto::fromInput(['rating' => '5', 'comment' => 'Nice pitch'])->rating);
    }

    public function testRegexPatternsMatchExpectedValues(): void
    {
        $this->assertSame(1, preg_match(RegexPatterns::PHONE, '+97333000001'));
        $this->assertSame(0, preg_match(RegexPatterns::PHONE, '33-00'));
        $this->assertSame(1, preg_match(RegexPatterns::RECORD_CODE, 'RSV-5T1W3E8R'));
        $this->assertSame(0, preg_match(RegexPatterns::USERNAME, 'has space'));
        $this->assertSame('[A-Za-z0-9\-]{1,20}', RegexPatterns::html(RegexPatterns::BUILDING_NUMBER));
    }
}
