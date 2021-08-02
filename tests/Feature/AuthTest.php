<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    /**
     * Data containig name, email, password "password" and confirmation password "password".
     *
     * @var array
     */
    private $user_data = [];

    public function setUp(): void
    {
        parent::setUp();

        // Get default fields from factory and overwrite password fields.
        $this->user_data = User::factory()->definition();
        $this->user_data['password'] = 'password';
        $this->user_data['password_confirmation'] = 'password';
    }

    public function testRegisterRequiredFields()
    {
        $this
            ->post('register')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot create user.',
                'errors' => [
                    'name' => ['Missing user name.'],
                    'email' => ['Missing email.'],
                    'password' => ['Missing password.']
                ]
            ]);
    }

    public function testRegisterInvalidEmail()
    {
        $this->user_data['email'] = 'invalid email address';

        $this
            ->post('register', $this->user_data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot create user.',
                'errors' => [
                    'email' => ['The email must be a valid email address.']
                ]
            ]);
    }

    public function testRegisterPasswordMatch()
    {
        $this->user_data['password_confirmation'] = 'password2';

        $this
            ->post('register', $this->user_data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot create user.',
                'errors' => [
                    'password' => ['Passwords do not match.']
                ]
            ]);
    }

    public function testRegisterUserSuccess()
    {
        // Create a user and check if token exists and structure is correct.
        $this
            ->createUser()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'id'
                ],
                'token'
            ]);
    }

    public function testRegisterUserExists()
    {
        // Create a random user once.
        $this->createUser();

        // Create same user the second time.
        $this
            ->createUser()
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot create user.',
                'errors' => [
                    'email' => ['User with this e-mail already exists.']
                ]
            ]);
    }

    public function testLoginRequiredFields()
    {
        $this
            ->post('login')
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot log in.',
                'errors' => [
                    'email' => ['Missing email.'],
                    'password' => ['Missing password.']
                ]
            ]);
    }

    public function testLoginUserNotExists()
    {
        // Create a user and get a response of created user.
        $user = json_decode($this->createUser()->getContent(), true);

        /*
         * It is possible to log in again after user is already logged in once. It will simply create another token.
         * So it is not necessary to log out the user first.
         */

        // Try Log in with non-existing email.
        $this->user_data = [
            'email' => 'non-existent-user-email@this-host-does-not-exist.this-also-does-not-exist',
            'password' => 'password'
        ];

        $this
            ->post('login', $this->user_data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot log in.',
                'errors' => [
                    'user' => ['Invalid user e-mail or password.']
                ]
            ]);
        
        // Try Log in with non-existing password for that e-mail.
        $this->user_data = [
            'email' => $user['user']['email'],
            'password' => 'this-password-is-not-valid-for-this-email'
        ];

        $this
            ->post('login', $this->user_data)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'Cannot log in.',
                'errors' => [
                    'user' => ['Invalid user e-mail or password.']
                ]
            ]);
    }

    public function testLoginUserSuccess()
    {
        // Create a user and get a response of created user.
        $user = json_decode($this->createUser()->getContent(), true);

        /*
         * It is possible to log in again after user is already logged in once. It will simply create another token.
         * So it is not necessary to log out the user first.
         */
        $this->user_data = [
            'email' =>  $user['user']['email'],
            'password' => 'password'
        ];

        $this
            ->post('login', $this->user_data)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'id'
                ],
                'token'
            ]);
    }

    public function testLogoutUserNoToken()
    {
        // If user was not logged in, it cannot be logged out. In this case there are actually no users in DB.
        $this
            ->post('logout')
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testLogoutUserInvalidToken()
    {
        // Create a user. User is logged in at creation.
        json_decode($this->createUser()->getContent(), true);

        $this
            ->post('logout', [], ['Authorization' => 'Bearer INVALID-TOKEN'])
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testLogoutUserSuccess()
    {
        // Create a user and get a response of created user. User is logged in at creation.
        $user = json_decode($this->createUser()->getContent(), true);
 
        $this->user_data = [
            'email' =>  $user['user']['email'],
            'password' => 'password'
        ];

        // Log in the user we just created.
        $reponse = $this
            ->post('login', $this->user_data)
            ->getContent();

        $user = json_decode($reponse, true);

        $this
            ->post('logout', [], ['Authorization' => 'Bearer '.$user['token']])
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'message' => 'Logged out.'
            ]);
    }

    /**
     * Create a user with factory settings. Function used to create same user multiple times.
     *
     * @return Illuminate\Testing\TestResponse
     */
    private function createUser()
    {
        return $this->post('register', $this->user_data);
    }

    /**
     * Visit the given URI with a POST request.
     *
     * @param string $uri
     * @param array $data
     * @param array $headers
     *
     * @return Illuminate\Testing\TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $headers += ['Accept' => 'application/json'];

        $uri = sprintf('api/%s', $uri);

        return parent::post($uri, $data, $headers);
    }
}
