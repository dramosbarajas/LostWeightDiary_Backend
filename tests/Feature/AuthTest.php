<?php

namespace Tests\Feature;

use App\User;
use Faker\Factory;
use Tests\TestCase;
use Tests\CreatesApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use WithFaker;
    use CreatesApplication, DatabaseMigrations, RefreshDatabase;


    /**
     * testRegister
     * Registro de un nuevo usuario 
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

    /**
     * testEmailExists
     * Test para registrar un usuario que ya existe en base de datos 
     * @return void
     */
    public function testEmailExists()
    {
        $user = factory(User::class)->create();

        $data = [
            'name' => $this->faker->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $this->json('POST', route('register'), $data)
            ->assertStatus(422)
            ->assertJsonFragment([
                'email' => ['El valor del campo email ya está en uso.'],
            ]);
    }

    /**
     * testErrorConfirmPassword
     * Test para detectar que la confirmacion de contraseña es erronea
     * @return void
     */
    public function testErrorConfirmPassword()
    {
        //Payload
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password1',
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $response = $this->json('POST', route('register'), $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'password' => ['El campo confirmación de password no coincide.'],
                ]
            ]);
    }

    /**
     * testRequireEmailAndLogin
     * Test para los campos requeridos en el login
     * @return void
     */
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


    /**
     * testLogin
     * Test que comprueba el correcto login de un usuario
     * @return void
     */
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

    public function testlogoutUser()
    {
        $user = factory(User::class)->create();
        $user = [
            'email' => $user->email,
            'password' => 'password'
        ];

        Auth::attempt($user);
        $token = Auth::user()->createToken('test_client_token')->accessToken;
        $headers = ['Authorization' => "Bearer $token"];
        $this->json('GET', 'api/logout', [], $headers)
            ->assertStatus(204);
    }
}
