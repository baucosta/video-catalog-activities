<?php

namespace Tests\Feature;

use App\Http\Controllers\VideoController;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Video;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations, TestValidations, TestSaves;
    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->video = factory(Video::class)->create([
            'opened' => false
        ]);
        $this->sendData = [
            'title' => 'title',
            'description' => 'description',
            'year_launched' => 2010,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90
        ];
    }

    public function testRollbackStore() {
        $this->assertRollbackStore($this->sendData);
    }

    public function testRollbackUpdate() {
        $this->assertRollbackUpdate(['name' => 'test'], $this->video);
    }


    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response
            ->assertStatus(200)
            ->assertJson($this->video->toArray());
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

    public function testSyncCategories() {
        $categoriesID = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categoriesID);
        $genreID = $genre->id;

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + [
                'genres_id' => [$genreID],
                'categories_id' => [$categoriesID[0]]
            ]
        );

        $this->assertHasRelationshipRegister(
            'category_video',
            [
                'video_id' => $response->json('id'),
                'category_id' => $categoriesID[0]
            ]
        );

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'genres_id' => [$genreID],
                'categories_id' => [$categoriesID[1],$categoriesID[2]]
            ]
        );

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesID[0],
            'video_id' => $response->json('id')
        ]);

        $this->assertHasRelationshipRegister(
            'category_video',
            [
                'video_id' => $response->json('id'),
                'category_id' => $categoriesID[1]
            ]
        );

        $this->assertHasRelationshipRegister(
            'category_video',
            [
                'video_id' => $response->json('id'),
                'category_id' => $categoriesID[2]
            ]
        );
    }

    public function testSyncGenres() {
        $genres = factory(Genre::class, 3)->create();
        $genreID = $genres->pluck('id')->toArray();
        $categoryID = factory(Category::class)->create()->id;
        $genres->each(function($genre) use ($categoryID) {
            $genre->categories()->sync($categoryID);
        });

        $response = $this->json(
            'POST',
            $this->routeStore(),
            $this->sendData + [
                'genres_id' => [$genreID[0]],
                'categories_id' => [$categoryID]
            ]
        );

        $this->assertHasRelationshipRegister(
            'genre_video',
            [
                'video_id' => $response->json('id'),
                'genre_id' => $genreID[0]
            ]
        );

        $response = $this->json(
            'PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'genres_id' =>[$genreID[1],$genreID[2]],
                'categories_id' => [$categoryID]
            ]
        );

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genreID[0],
            'video_id' => $response->json('id')
        ]);

        $this->assertHasRelationshipRegister(
            'genre_video',
            [
                'video_id' => $response->json('id'),
                'genre_id' => $genreID[1]
            ]
        );

        $this->assertHasRelationshipRegister(
            'genre_video',
            [
                'video_id' => $response->json('id'),
                'genre_id' => $genreID[2]
            ]
        );
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
}
