<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testList()
    {
        factory(Category::class, 1)->create();
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $categoryKeys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
            ],
            $categoryKeys
        );
    }

    public function testCreate() {
        $category = Category::create([
            'name' => 'firsttest'
        ]);
        $category->refresh();

        $this->assertEquals('firsttest', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue((bool)$category->is_active);
        // $this->assertEquals(36, strlen($category->id));
        $this->assertTrue((bool)Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'firsttest',
            'description' => null
        ]);
        $this->assertNull($category->description);
        $this->assertTrue((bool)Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'firsttest',
            'description' => 'description_test'
        ]);
        $this->assertEquals('description_test', $category->description);
        $this->assertTrue((bool)Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'firsttest',
            'is_active' => false
        ]);
        $this->assertFalse($category->is_active);
        $this->assertTrue((bool)Uuid::isValid($category->id));

        $category = Category::create([
            'name' => 'firsttest',
            'is_active' => true
        ]);
        $this->assertTrue($category->is_active);
        $this->assertTrue((bool)Uuid::isValid($category->id));
    }

    public function testUpdate() {
        $category = factory(Category::class)->create([
            'description' => 'firsttest',
            'is_active' => false
        ]);

        $data = [
            'name' => 'first update',
            'description' => 'description for update',
            'is_active' => true
        ];
        $category->update($data);

        foreach($data as $key=>$value) {
            $this->assertEquals($value, $category->{$key});
        }

    }

    public function testDelete() {
        $category = factory(Category::class)->create([
            'description' => 'firsttest',
            'is_active' => false
        ]);

        $this->assertTrue($category->delete());

    }

    /*public function testDelete() {
        $category = factory(Category::class)->create();
        $category->delete();
        $this->assertNull(Category::find($category->id));

        $category->restore();
        $this->assertNotNull(Category::find($category->id));

    }*/
}
