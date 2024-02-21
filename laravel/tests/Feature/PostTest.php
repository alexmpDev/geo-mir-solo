<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;


class PostTest extends TestCase
{
    public static User $testUser;           
    public static array $testUserData = []; 

    public static array $testUserData2 = []; 
    public static array $testPost = [];
    public static array $validData = [];
    public static array $invalidData = [];
  
   public static function setUpBeforeClass() : void
   {
        parent::setUpBeforeClass();
        // Creem usuari/a de prova
        $name = "test_" . time();
        self::$testUserData = [
            "name"      => "{$name}",
            "email"     => "{$name}@mailinator.com",
            "password"  => "12345678"
        ];
        self::$testUserData2 = [
            "name"      => "{$name}",
            "email"     => "{$name}@mailnator.com",
            "password"  => "12345678"
        ];

        // TODO Omplir amb dades vàlides
        self::$validData = [
            'body'      => 'Andrés',
            'upload'    =>  UploadedFile::fake()->image('foto.png')->size(500),
            'latitude'  => '12',
            'longitude' => '12',
            'author_id' => '1',
        ];
            
        self::$invalidData = [
            'body'      => null,
            'upload'    =>  'foto',
            'latitude'  => null,
            'longitude' => null,
            'author_id' => '1',
        ];
   }

   public function test_post_first()
   {
       // Desem l'usuari al primer test
       self::$testUser = new User(self::$testUserData);
       self::$testUser->save();
       // Comprovem que s'ha creat
       $this->assertDatabaseHas('users', [
           'email' => self::$testUserData['email'],
       ]);
   }


   public function test_post_auth_operation()
    {
        Sanctum::actingAs(self::$testUser);
        // TODO Lògica del test
        $response = $this->get('/api/posts');
        $response->assertOk()->assertStatus(200);
    }

    public function test_posts_guest_operation()
    {
        // TODO Lògica del test
        $response = $this->get('/api/posts');
        $response->assertStatus(302);
    }


   public function test_post_create()
   {
       Sanctum::actingAs(self::$testUser);
       // Cridar servei web de l'API
       $response = $this->postJson("/api/posts", self::$validData);
       // Revisar que no hi ha errors de validació
       $params = array_keys(self::$validData);
       $response->assertValid($params);
       // TODO Revisar més errors
   }


   public function test_post_create_error()
   {
       Sanctum::actingAs(self::$testUser, ['*']);
       // Cridar servei web de l'API
       $response = $this->postJson("/api/posts", self::$invalidData);
       Log::debug(self::$testUser);
       Log::debug((array)$response->getData());
       // TODO Revisar errors de validació
       $params = ['body', 'upload', 'latitude', 'longitude'];
       $response->assertInvalid($params);
       // TODO Revisar més errors
   }


//    // TODO Sub-tests de totes les operacions CRUD


    public function test_posts_last()
    {
        // Eliminem l'usuari al darrer test
        $user = new User(self::$testUserData2);
        $user->delete();
        // Comprovem que s'ha eliminat
        $this->assertDatabaseMissing('users', [
            'email' => self::$testUserData2['email'],
        ]);
    }


    public function test_posts_like()
    {   
        Sanctum::actingAs(new User(self::$testUserData));
        $response = $this->postJson("/api/posts/2/like");
        $response->assertJsonFragment(['success' => true]);
    }
    public function test_posts_unlike()
    {
        Sanctum::actingAs(new User(self::$testUserData));
        $response = $this->delete("/api/posts/2/like", self::$validData);

        $this->assertDatabaseMissing('likes', [
            'post_id' => 1, 
        ]);
    }

    public function test_post_comments()
    {
        Sanctum::actingAs(new User(self::$testUserData));
        $response = $this->postJson("/api/posts/2/comments", [
            'comment' => 'Este es un comentario de prueba',
        ]);
        $response->assertStatus(201); 
        $response->assertJsonStructure([
            "success",
            "data",
            "comment",
        ]);
    }
}
