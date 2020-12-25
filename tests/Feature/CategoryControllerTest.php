<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Category;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations;
    private $category;

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
            ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->category->toArray());
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));

        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test',
            'description' => 'some description',
            'is_active' => false
        ]);

        $response
            ->assertJsonFragment([
                'is_active' => false,
                'description' => 'some description'
            ]);
    }

    public function testUpdate() {
        $category = factory(Category::class)->create([
            'description' => 'some description',
            'is_active' => false
        ]);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'description' => 'test'
            ]);

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => '',
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);

        $category->description = 'test';
        $category->save();

        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => 'test',
                'description' => null,
            ]
        );

        $response->assertJsonFragment([
            'description' => null
        ]);
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
}
