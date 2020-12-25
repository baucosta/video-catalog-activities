<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Lang;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testInValidationData() {
        $response = $this->json('POST', route('genres.store'), []);

        $this->assertInValidationRequired($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]);

        $this->assertInValidationMax($response);
        $this->assertInValidationBoolean($response);

        $genre = factory(Genre::class)->create();
        $response = $this->json('PUT', route('genres.update', ['genre' => $genre->id]), []);

        $this->assertInValidationRequired($response);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

        $this->assertInValidationMax($response);
        $this->assertInValidationBoolean($response);

    }

    protected function assertInValidationRequired(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.required', ['attribute' => 'name'])
            ]);

    }

    protected function assertInValidationMax(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);

    }

    protected function assertInValidationBoolean(TestResponse $response) {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);

    }

    public function testStore() {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test'
        ]);

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));

        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test',
            'is_active' => false
        ]);

        $response
            ->assertJsonFragment([
                'is_active' => false,
            ]);
    }

    public function testUpdate() {
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);

        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            [
                'name' => 'test',
                'is_active' => true
            ]
        );

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment([
                'is_active' => true
            ]);
    }

    public function testDelete() {
        /*$genre = factory(Genre::class)->create([
            'name' => 'some name',
            'is_active' => false
        ]);*/
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id])
        );

        $response
            ->assertStatus(204);

        // $this->assertNull(Genre::find($genre->id));
        // $this->assertNotNull(Genre::withoutTrashed()->find($genre->id));
    }
}
