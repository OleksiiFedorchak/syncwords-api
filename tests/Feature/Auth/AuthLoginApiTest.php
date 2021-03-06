<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\SyncWordsTestCase;
use Tests\TestCase;

class AuthLoginApiTest extends SyncWordsTestCase
{
    /**
     * Validation enabled, so correct data needs to be passed
     *
     * @test
     * @return void
     */
    public function assert_validation_enabled()
    {
        $this->postJson(route('api.auth.login'))
            ->assertStatus(422);

        $password = 'password';

        $user = User::factory()
            ->create([
                'password' => Hash::make($password),
            ]);

        $this->postJson(route('api.auth.login'), ['email' => $user->email])
            ->assertStatus(422);

        $this->postJson(route('api.auth.login'), ['password' => $password])
            ->assertStatus(422);
    }

    /**
     * Validation enabled, so correct credential needs to be passed
     *
     * @test
     * @return void
     */
    public function assert_valid_credential()
    {
        $user = User::factory()
            ->create([
                'password' => Hash::make('password'),
            ]);

        $this->postJson(route('api.auth.login'), [
            'email' => $user->email,
            'password' => 'not_valid_password'
        ])
            ->assertStatus(401);
    }

    /**
     * Response should be {'access_token' => '...', 'token_type' => Bearer}
     * Second response should be {'message' => '...'}
     *
     * @test
     * @return void
     */
    public function assert_correct_json_structure_returned()
    {
        $user = User::factory()
            ->create([
                'password' => Hash::make('password'),
            ]);

        $this->postJson(route('api.auth.login'), [
            'email' => $user->email,
            'password' => 'password'
        ])
            ->assertStatus(200)
            ->assertJson(['token_type' => 'Bearer'])
            ->assertJsonStructure([
                'access_token',
                'token_type'
            ]);
    }
}
