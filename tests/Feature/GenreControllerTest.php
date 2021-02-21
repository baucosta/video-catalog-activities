<?php

namespace Tests\Feature;

use App\Http\Controllers\GenreController;
use App\Http\Resources\GenreResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\Category;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;
    private $genre;
    private $serializedFields = [
        'id',
        'name',
        'is_active',
        'categories',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(200)
            ->assertJson($this->assertQuantityPaginate)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);

        $resource = $this->resource()::collection(collect([$this->genre]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $this->assertResourceForFind($response);
    }

    public function testInValidationData() {
        $data = [
            'name' => '',
            'categories_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'is_active' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = [
            'categories_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'categories_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();
        $data = [
            'categories_id' => [$category->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testStore() {
        $categoryID = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test'
        ];

        $response = $this->assertStore(
            $data + ['categories_id' => [$categoryID]],
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);


        $this->assertHasRelationshipRegister(
            'category_genre',
            [
                'genre_id' => $response->json('data.id'),
                'category_id' => $categoryID
            ]
        );

        $data = [
            'name' => 'test',
            'is_active' => false
        ];
        $this->assertStore(
            $data + ['categories_id' => [$categoryID]],
            $data + ['is_active' => false, 'deleted_at' => null]
        );

        $this->assertResourceForFind($response);
    }

    public function testUpdate() {
        $categoryID = factory(Category::class)->create()->id;
        $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate(
            $data + ['categories_id' => [$categoryID]],
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $this->assertResourceForFind($response);

        $this->assertHasRelationshipRegister(
            'category_genre',
            [
                'genre_id' => $response->json('data.id'),
                'category_id' => $categoryID
            ]
        );
    }

    public function testSyncCategories() {
        $categoriesID = factory(Category::class, 3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesID[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertHasRelationshipRegister(
            'category_genre',
            [
                'genre_id' => $response->json('data.id'),
                'category_id' => $categoriesID[0]
            ]
        );

        $sendData = [
            'name' => 'test',
            'categories_id' => [$categoriesID[1], $categoriesID[2]]
        ];
        $response = $this->json(
            'PUT',
            route('genres.update',['genre' => $response->json('data.id')]),
            $sendData
        );
        $this->assertDatabaseMissing('category_genre', [
            'category_id' => $categoriesID[0],
            'genre_id' => $response->json('data.id')
        ]);

        $this->assertHasRelationshipRegister(
            'category_genre',
            [
                'genre_id' => $response->json('data.id'),
                'category_id' => $categoriesID[1]
            ]
        );

        $this->assertHasRelationshipRegister(
            'category_genre',
            [
                'genre_id' => $response->json('data.id'),
                'category_id' => $categoriesID[2]
            ]
        );
    }

    public function testDelete() {
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $this->genre->id])
        );

        $response
            ->assertStatus(204);

        // $this->assertNull(Genre::find($this->genre->id));
        // $this->assertNotNull(Genre::withoutTrashed()->find($this->genre->id));
    }

    // public function testRollbackStore() {
    //     $this->assertRollbackStore(['name' => 'test']);
    // }

    // public function testRollbackUpdate() {
    //     $this->assertRollbackUpdate(['name' => 'test'], $this->genre);
    // }

    protected function routeStore() {
        return route('genres.store');
    }

    protected function routeUpdate() {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model() {
        return Genre::class;
    }

    protected function controller() {
        return GenreController::class;
    }

    protected function resource()
    {
        return GenreResource::class;
    }
}
