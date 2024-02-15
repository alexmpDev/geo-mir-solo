<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;


class PlaceTest extends TestCase
{
    public static array $testUser = [];
    public static array $testUser2 = [];
    public static array $validData = [];
    public static array $invalidData = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Creem usuari/a de prova
        $name = "test_" . time();
        self::$testUser = [
            "name"      => "{$name}",
            "email"     => "{$name}@mailinator.com",
            "password"  => "12345678"
        ];

        self::$testUser2 = [
            "name"      => "{$name}",
            "email"     => "{$name}@mailnator.com",
            "password"  => "12345678"
        ];


        // TODO Omplir amb dades vàlides
        self::$validData = [
            'name'        => 'Alex',
            'description' => 'Esto es un Place de prueba',
            'upload'      => UploadedFile::fake()->image('foto.png')->size(500),
            'latitude'    => '21.2222',
            'longitude'   => '30.4444',
            'author_id'   => 1,
        ];
        // TODO Omplir amb dades incorrectes
        self::$invalidData = [
            'name'        => null,
            'description' => null,
            'upload'      => 'foto',
            'latitude'    => null,
            'longitude'   => null,
            'author_id'   => 1,
        ];
    }

    public function test_places_first()
    {
        // Desem l'usuari al primer test
        $user = new User(self::$testUser);
        $user->save();
        // Comprovem que s'ha creat
        $this->assertDatabaseHas('users', [
            'email' => self::$testUser['email'],
        ]);
    }


    public function test_places_auth_operation()
    {
        Sanctum::actingAs(new User(self::$testUser));
        //Lògica del test

        $response = $this->get('/api/places');
        $response->assertOk()->assertStatus(200);
    }


    public function test_places_guest_operation()
    {
        //Lògica del test
        $response = $this->get('/api/places');
        $response->assertStatus(302);
    }


    public function test_places_create()
    {
        Sanctum::actingAs(new User(self::$testUser));
        // Cridar servei web de l'API
        $response = $this->postJson("/api/places", self::$validData);
        // Revisar que no hi ha errors de validació
        $params = array_keys(self::$validData);
        $response->assertValid($params);

        //Revisar més errors
        $this->_test_ok($response);
    }


    public function test_places_create_error()
    {
        Sanctum::actingAs(new User(self::$testUser));
        // Cridar servei web de l'API
        $response = $this->postJson("/api/places", self::$invalidData);
        // TODO Revisar errors de validació
        $params = ['name', 'description', 'upload', 'latitude', 'longitude'];

        $response->assertInvalid($params);

        //Revisar més errors
        $this->_test_error($response);
    }


    // // TODO Sub-tests de totes les operacions CRUD

    public function test_places_show()
    {
        Sanctum::actingAs(new User(self::$testUser));
        // Cridar servei web de l'API
        $response = $this->postJson("/api/places/show", self::$validData);
        // Revisar que no hi ha errors de validació
        
        $response->assertJsonFragment(['success' => true]);
    }

    public function test_places_favorite_add()
    {
        Sanctum::actingAs(new User(self::$testUser));
        // Cridar servei web de l'API
        $response = $this->postJson("/api/places/1/favs", self::$validData);
        // Revisar que no hi ha errors de validació
        
        $response->assertJsonFragment(['success' => true]);
    }

    public function test_places_favorite_delete()
    {
        Sanctum::actingAs(new User(self::$testUser));
        // Cridar servei web de l'API
        $response = $this->delete("/api/places/1/favs", self::$validData);
        // Revisar que no hi ha errors de validació
        
        $this->assertDatabaseMissing('favorites', [
            'place_id' => 1,
        ]);
    }


    public function test_places_last()
    {
        // Eliminem l'usuari al darrer test
        $user = new User(self::$testUser2);
        $user->delete();
        // Comprovem que s'ha eliminat
        $this->assertDatabaseMissing('users', [
            'email' => self::$testUser2['email'],
        ]);
    }


    protected function _test_ok($response, $status = 200)
    {
        // Check JSON response
        $response->assertStatus($status);
        // Check JSON properties
        
        $response->assertJson([
            "success" => true,
        ]);
        // Check JSON dynamic values
        $response->assertJsonPath("data",
            fn ($data) => is_array($data)
        );
 
    }

    protected function _test_error($response)
    {
        // Check response
        $response->assertStatus(422);
        // Check validation errors
        $response->assertInvalid(["upload"]);
        // Check JSON properties
        $response->assertJson([
            "message" => true, // any value
            "errors"  => true, // any value
        ]);       
        // Check JSON dynamic values
        $response->assertJsonPath("message",
            fn ($message) => !empty($message) && is_string($message)
        );
        $response->assertJsonPath("errors",
            fn ($errors) => is_array($errors)
        );
    }
}
