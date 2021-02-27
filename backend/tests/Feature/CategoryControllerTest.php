<?php

namespace Tests\Feature;

use App\Http\Controllers\CategoryController;
use App\Http\Resources\CategoryResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Category;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;
    private $category;
    private $serializedFields = [
        'id',
        'name',
        'description',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }


    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

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


        $resource = $this->resource()::collection(collect([$this->category]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ]);

        $this->assertResourceForFind($response);
    }

    public function testInValidationData() {
        $data = [
            'name' => ''
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

    }

    public function testStore() {
        $data = [
            'name' => 'test'
        ];
        $response = $this->assertStore(
            $data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $data = [
            'name' => 'test',
            'description' => 'some description',
            'is_active' => false
        ];
        $this->assertStore(
            $data,
            $data + ['description' => 'some description', 'is_active' => false]
        );

        $this->assertResourceForFind($response);
    }

    public function testUpdate() {
        $data = [
            'name' => 'test',
            'description' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $this->assertResourceForFind($response);

        $data = [
            'name' => 'test',
            'description' => ''
        ];

        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));
    }

    public function testDelete() {
        /*$category = factory(Category::class)->create([
            'description' => 'some description',
            'is_active' => false
        ]);*/


        $response = $this->json(
            'DELETE',
            route('categories.destroy', ['category' => $this->category->id])
        );

        $response
            ->assertStatus(204);

        // $this->assertNull(Category::find($this->category->id));
        // $this->assertNotNull(Category::withoutTrashed()->find($this->category->id));
    }

    protected function routeStore() {
        return route('categories.store');
    }

    protected function routeUpdate() {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model() {
        return Category::class;
    }

    protected function controller() {
        return CategoryController::class;
    }

    protected function resource()
    {
        return CategoryResource::class;
    }
}
