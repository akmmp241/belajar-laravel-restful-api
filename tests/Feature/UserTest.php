<?php

namespace Tests\Feature;

use App\Http\Resources\UserResource;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            "username" => "akmal",
            "password" => "eekmu241",
            "name" => "Akmal Muhammad Pridianto"
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    "username" => "akmal",
                    "name" => "Akmal Muhammad Pridianto"
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            "username" => "",
            "password" => "",
            "name" => ""
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "The username field is required."
                    ],
                    "password" => [
                        "The password field is required."
                    ],
                    "name" => [
                        "The name field is required."
                    ]
                ]
            ]);
    }

    public function testRegisterUsernameAlreadyExist()
    {
        $this->testRegisterSuccess();

        $this->post('/api/users', [
            "username" => "akmal",
            "password" => "eekmu241",
            "name" => "Akmal Muhammad Pridianto"
        ])->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "username" => [
                        "username already registered"
                    ]
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            "username" => "test",
            "password" => "test12345",
            "name" => "test"
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $user = User::query()->where('username', 'test')->first();

        self::assertNotNull($user->token);
    }

    public function testLoginFailed()
    {
        $this->post('/api/users/login', [
            "username" => "test",
            "password" => "test12345",
            "name" => "test"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);

        $this->post('/api/users/login', [
            "username" => "test",
            "password" => "test",
            "name" => "test"
        ])->assertStatus(401)
            ->assertJson([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this->get('/api/users/current', [
            'Authorization' => 'salah'
        ])
            ->assertStatus(401)
            ->assertJson([
                "errors" => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ]);
    }

    public function testUpdateNameSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::query()->where('username', 'test')->first();

        $this->patch('/api/users/current',
            [
                'name' => 'Akmmp'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'Akmmp'
                ]
            ]);

        $newUser = User::query()->where('username', 'test')->first();

        self::assertNotEquals($oldUser->name, $newUser->name);
    }

    public function testUpdatePasswordSuccess()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::query()->where('username', 'test')->first();

        $this->patch('/api/users/current',
            [
                'password' => 'baru12345'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                "data" => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newUser = User::query()->where('username', 'test')->first();

        self::assertNotEquals($oldUser->password, $newUser->password);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::query()->where('username', 'test')->first();

        $this->patch('/api/users/current',
            [
                'name' => 'AkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridiantoAkmalMuhammadPridianto'
            ],
            [
                'Authorization' => 'test'
            ]
        )
            ->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "name" => [
                        "The name field must not be greater than 100 characters."
                    ]
                ]
            ]);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $user = User::query()->where('username', 'test')->first();

        self::assertNull($user->token);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->delete(uri: '/api/users/logout', headers: [
            'Authorization' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'Unauthorized'
                    ]
                ]
            ]);
    }
}
