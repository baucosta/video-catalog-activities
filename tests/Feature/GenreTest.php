<?php

namespace Tests\Feature;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();
        $genres = Genre::all();
        $this->assertCount(1, $genres);
        $genreKeys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'
            ],
            $genreKeys
        );
    }

    public function testCreate() {
        $genre = Genre::create([
            'name' => 'firstgenretest'
        ]);
        $genre->refresh();

        $this->assertEquals('firstgenretest', $genre->name);
        $this->assertTrue((bool)$genre->is_active);
        $this->assertTrue((bool)Uuid::isValid($genre->id));

        $genre = Genre::create([
            'name' => 'firstgenretest',
            'is_active' => false
        ]);
        $this->assertFalse($genre->is_active);
        $this->assertTrue((bool)Uuid::isValid($genre->id));

        $genre = Genre::create([
            'name' => 'firstgenretest',
            'is_active' => true
        ]);
        $this->assertTrue($genre->is_active);
        $this->assertTrue((bool)Uuid::isValid($genre->id));
    }

    public function testUpdate() {
        $genre = factory(Genre::class)->create([
            'name' => 'firstgenretest',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'first genre update',
            'is_active' => true
        ];
        $genre->update($data);

        foreach($data as $key=>$value) {
            $this->assertEquals($value, $genre->{$key});
        }

    }

    public function testDelete() {
        $genre = factory(Genre::class)->create([
            'name' => 'firstgenretest',
            'is_active' => false
        ])->first();

        $this->assertTrue($genre->delete());

    }
}
