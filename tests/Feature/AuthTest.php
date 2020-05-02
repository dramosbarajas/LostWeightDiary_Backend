<?php

namespace Tests\Feature;

use App\User;
use Faker\Factory;
use Tests\TestCase;
use Tests\CreatesApplication;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use WithFaker;
    use CreatesApplication, DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('passport:install');
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRegister()
    {
        //Payload
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $this->json('POST', route('register'), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'name',
                'email',
                'age',
                'updated_at',
                'created_at',
                'id',
            ]);
    }

    public function testRequireEmailAndLogin()
    {
        $this->json('POST', 'api/login')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['El campo email es obligatorio.'],
                    'password' => ['El campo password es obligatorio.']
                ]
            ]);
    }


    public function testLogin()
    {
        //Create a new user 

        $user = factory(User::class)->create();
        $user->verification_token = null;
        $user->isVerified = 1;
        $user->save();
        //Payload
        $data = [
            'email' => $user->email,
            'password' => 'password',
        ];
        $this->json('POST', route('login'), $data)
            ->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'User'
            ]);
    }
}
