<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomUserTest extends TestCase
{
    public function test_new_user_is_persisted_and_its_credentials_are_validated(): void
    {
        $name = getenv('ECOMERCE_TEST_USER_NAME');
        $email = getenv('ECOMERCE_TEST_USER_EMAIL');
        $name = $name === false ? 'Cliente de prueba' : trim($name);
        $email = $email === false ? 'cliente-'.bin2hex(random_bytes(8)).'@example.test' : trim($email);

        $this->assertTrue($name !== '' && mb_strlen($name) <= 255, 'Provide a test name of 1 to 255 characters.');
        $this->assertTrue(strlen($email) <= 255 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false, 'Provide a valid synthetic email.');

        $this->migrateIsolatedSqlite();

        $password = bin2hex(random_bytes(24));
        $this->assertDatabaseMissing('users', ['email' => $email]);

        User::create(['name' => $name, 'email' => $email, 'password' => $password]);

        $user = User::query()->where('email', $email)->sole();
        $this->assertSame(['name' => $name, 'email' => $email], $user->only(['name', 'email']));
        $this->assertTrue(Hash::isHashed($user->password), 'The password must be stored as a hash.');
        $this->assertFalse(array_key_exists('password', $user->toArray()), 'The password must stay out of serialization.');
        $this->assertTrue(Auth::validate(['email' => $email, 'password' => $password]));
        $this->assertFalse(Auth::validate(['email' => $email, 'password' => $password.'-incorrect']));
    }
}
