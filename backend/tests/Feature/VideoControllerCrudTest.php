<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use App\Http\Controllers\VideoController;
use App\Http\Resources\VideoResource;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Arr;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerCrudTest extends BaseVideoControllerTestCase {
    use TestValidations, TestSaves, TestResources;

    private $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'rating',
        'duration',
        'opened',
        'thumb_file_url',
        'banner_file_url',
        'video_file_url',
        'trailer_file_url',
        'created_at',
        'updated_at',
        'deleted_at',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ]
    ];

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);

        $resource = $this->resource()::collection(collect([$this->video]));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJsonFragment($this->video->toArray());

        $this->assertResourceForFind($response);
    }

    public function testInvalidationRequired() {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
            'categories_id' => '',
            'genres_id' => ''
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax() {
        $data = [
            'title' => str_repeat('a', 256)
        ];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger() {
        $data = [
            'duration' => 's'
        ];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidationYearLaunchedField() {
        $data = [
            'year_launched' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpenedField() {
        $data = [
            'opened' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidationRatingField() {
        $data = [
            'rating' => 0
        ];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesIdField() {
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

    public function testInvalidationGenresIdField() {
        $data = [
            'genres_id' => 'a'
        ];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = [
            'genres_id' => [100]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();
        $data = [
            'genres_id' => [$genre->id]
        ];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testSave() {
        $category = factory(Category::class)->create();
        $genres = factory(Genre::class)->create();
        $genres->categories()->sync($category->id);

        $data = [
            [
                'send_data' => $this->sendData + [
                    'categories_id' => [$category->id],
                    'genres_id' => [$genres->id],
                ],
                'test_data' => $this->sendData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'opened' => true,
                    'categories_id' => [$category->id],
                    'genres_id' => [$genres->id],
                ],
                'test_data' => $this->sendData + ['opened' => true]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[1],
                    'categories_id' => [$category->id],
                    'genres_id' => [$genres->id],
                ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]]
            ],
        ];

        foreach($data as $key=>$value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);

            $this->assertHasRelationshipRegister(
                'category_video',
                [
                    'video_id' => $response->json('id'),
                    'category_id' => $value['send_data']['categories_id'][0]
                ]
            );

            $this->assertHasRelationshipRegister(
                'genre_video',
                [
                    'video_id' => $response->json('id'),
                    'genre_id' => $value['send_data']['genres_id'][0]
                ]
            );

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'created_at', 'updated_at'
            ]);

            $this->assertHasRelationshipRegister(
                'category_video',
                [
                    'video_id' => $response->json('id'),
                    'category_id' => $value['send_data']['categories_id'][0]
                ]
            );

            $this->assertHasRelationshipRegister(
                'genre_video',
                [
                    'video_id' => $response->json('id'),
                    'genre_id' => $value['send_data']['genres_id'][0]
                ]
            );
        }
    }

    public function testSavedWithoutFiles() {
        // $category = factory(Category::class)->create();
        // $genre = factory(Genre::class)->create();
        // $genre->categories()->sync($category->id);
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);

        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'opened' => true
                ],
                'test_data' => $testData + ['opened' => false]
            ],
            [
                'send_data' => $this->sendData + [
                    'rating' => Video::RATING_LIST[1]
                ],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1]]
            ]
        ];

        foreach($data as $key => $value) {
            $response = $this->assertStore(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertHasCategory(
                $response->json('data.id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('data.id'),
                $value['send_data']['genres_id'][0]
            );

            $response = $this->assertUpdate(
                $value['send_data'],
                $value['test_data'] + ['deleted_at' => null]
            );
            $response->assertJsonStructure([
                'data' => $this->serializedFields
            ]);
            $this->assertHasCategory(
                $response->json('data.id'),
                $value['send_data']['categories_id'][0]
            );
            $this->assertHasGenre(
                $response->json('data.id'),
                $value['send_data']['genres_id'][0]
            );
        }
    }

    protected function assertHasCategory($videoID, $categoryID) {
        $this->assertDatabaseHas('category_video', [
            'video_id' => $videoID,
            'category_id' => $categoryID
        ]);
    }

    protected function assertHasGenre($videoID, $genreID) {
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $videoID,
            'genre_id' => $genreID
        ]);
    }

    public function testDelete() {
        $response = $this->json(
            'DELETE',
            route('videos.destroy', ['video' => $this->video->id])
        );
        $response
            ->assertStatus(204);

        // $this->assertNull(Video::find($this->video->id));
        // $this->assertNotNull(Video::withoutTrashed()->find($this->video->id));
    }

    protected function routeStore() {
        return route('videos.store');
    }

    protected function routeUpdate() {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model() {
        return Video::class;
    }

    protected function controller() {
        return VideoController::class;
    }

    protected function resource()
    {
        return VideoResource::class;
    }

}
