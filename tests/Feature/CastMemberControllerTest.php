<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\CastMember;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    private $cast_member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cast_member = factory(CastMember::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->cast_member->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->cast_member->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->cast_member->toArray());
    }

    public function testInValidationData() {
        $data = [
            'name' => '',
            'type' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = [
            'name' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = [
            'type' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');

    }

    public function testStore() {
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);

        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_DIRECTOR
        ];

        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testUpdate() {
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'created_at', 'updated_at'
        ]);
    }

    public function testDelete() {
        $response = $this->json(
            'DELETE',
            route('cast_members.destroy', ['cast_member' => $this->cast_member->id])
        );

        $response
            ->assertStatus(204);

        // $this->assertNull(CastMember::find($this->cast_member->id));
        // $this->assertNotNull(CastMember::withoutTrashed()->find($this->cast_member->id));
    }

    protected function routeStore() {
        return route('cast_members.store');
    }

    protected function routeUpdate() {
        return route('cast_members.update', ['cast_member' => $this->cast_member->id]);
    }

    protected function model() {
        return CastMember::class;
    }
}
