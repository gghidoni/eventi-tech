<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Fortify;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    protected CreateNewUser $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateNewUser();
    }

    public function test_creates_user_with_valid_data(): void
    {
        $data = [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $user = $this->action->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_validates_required_fields(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([]);
    }

    public function test_validates_email_format(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name'                  => 'Test User',
            'email'                 => 'invalid-email',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    public function test_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->expectException(ValidationException::class);

        $this->action->create([
            'name'                  => 'Test User',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);
    }

    public function test_validates_password_requirements(): void
    {
        $this->expectException(ValidationException::class);

        $this->action->create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'short',
        ]);
    }

    public function test_hashes_password(): void
    {
        $data = [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $user = $this->action->create($data);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
