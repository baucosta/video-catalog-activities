<?php

namespace Tests\Feature;

use App\Http\Controllers\CastMemberController;
use App\Http\Resources\CastMemberResource;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\CastMember;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves, TestResources;
    private $cast_member;
    private $serializedFields = [
        'id',
        'name',
        'type',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

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
            ->assertJson($this->assertQuantityPaginate)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);


        $resource = $this->resource()::collection(collect([$this->cast_member]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->cast_member->id]));

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
            'data' => $this->serializedFields
        ]);

        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_DIRECTOR
        ];

        $response = $this->assertStore(
            $data,
            $data + ['deleted_at' => null]
        );

        $this->assertResourceForFind($response);
    }

    public function testUpdate() {
        $data = [
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);

        $this->assertResourceForFind($response);
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

    protected function controller() {
        return CastMemberController::class;
    }

    protected function resource()
    {
        return CastMemberResource::class;
    }
}
