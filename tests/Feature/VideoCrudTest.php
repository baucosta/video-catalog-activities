<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;

class VideoCrudTest extends BaseVideoTestCase {

    private $fileFieldsData = [];

    protected function setUp(): void
    {
        parent::setUp();
        foreach(Video::$fileFields as $field) {
            $this->fileFieldsData[$field] = "$field.test";
        }
    }

    public function testList() {
        factory(Video::class)->create();
        $videos = Video::all();
        $this->assertCount(1, $videos);
        $videoKeys = array_keys($videos->first()->getAttributes());

        $this->assertEqualsCanonicalizing([
              'id',
              'title',
              'description',
              'year_launched',
              'opened',
              'rating',
              'duration',
              'video_file',
              'thumb_file',
              'created_at',
              'updated_at',
              'deleted_at'
        ], $videoKeys);
    }

    public function testCreatedWithBasicFields() {
        $video = Video::create($this->data + $this->fileFieldsData);
        $video->refresh();

        $this->assertEquals(36, strlen($video->id));
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + $this->fileFieldsData + ['opened' => false]);

        $video = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', ['opened' => true]);
    }

    public function testCreatedWithRelations() {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();

        $video = Video::create($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);


        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testUpdatedWithBasicFields() {
        $video = factory(Video::class)->create(
            ['opened' => false]
        );
        $video->update($this->data + $this->fileFieldsData);
        $this->assertFalse($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $video = factory(Video::class)->create(
            ['opened' => false]
        );

        $video->update($this->data + $this->fileFieldsData + ['opened' => true]);
        $this->assertTrue($video->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testUpdateWithRelations() {
        $category = factory(Category::class)->create();
        $genre = factory(Genre::class)->create();
        $video = factory(Video::class)->create();

        $video->update($this->data + [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);


        $this->assertHasCategory($video->id, $category->id);
        $this->assertHasGenre($video->id, $genre->id);
    }

    public function testRollbackCreate() {
        $hasError = false;
        try{
            Video::create([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        } catch(QueryException $exception) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }

        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate() {
        $video = factory(Video::class)->create();
        $hasError = false;
        $oldTitle = $video->title;

        try{
            $video->update([
                'title' => 'title',
                'description' => 'description',
                'year_launched' => 2010,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'categories_id' => [0, 1, 2]
            ]);
        } catch(QueryException $exception) {
            $this->assertDatabaseHas('videos', [
                'title' => $oldTitle
            ]);
            $hasError = true;
        }

        $this->assertTrue($hasError);

    }

    public function testHandleRelations() {
        $video = factory(Video::class)->create();
        Video::handleRelations($video, []);
        $this->assertCount(0, $video->categories);
        $this->assertCount(0, $video->genres);

        $category = factory(Category::class)->create();
        Video::handleRelations($video, ['categories_id' => [$category->id]]);
        $video->refresh();
        $this->assertCount(1, $video->categories);

        $genre = factory(Genre::class)->create();
        Video::handleRelations($video, ['genres_id' => [$genre->id]]);
        $video->refresh();
        $this->assertCount(1, $video->genres);

        // $video->categories()->delete();
        // $video->genres()->delete();

        Video::handleRelations($video, [
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id]
        ]);
        $video->refresh();
        $this->assertCount(1, $video->categories);
        $this->assertCount(1, $video->genres);
    }

    public function testSyncCategories() {
        $categoriesID = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, ['categories_id' => [$categoriesID[0]]]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesID[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'categories_id' => [$categoriesID[1], $categoriesID[2]]
        ]);

        $this->assertDatabaseMissing('category_video', [
            'category_id' => $categoriesID[0],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesID[1],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('category_video', [
            'category_id' => $categoriesID[2],
            'video_id' => $video->id
        ]);
    }

    public function testSyncGenres() {
        $genresID = factory(Genre::class, 3)->create()->pluck('id')->toArray();
        $video = factory(Video::class)->create();
        Video::handleRelations($video, ['genres_id' => [$genresID[0]]]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresID[0],
            'video_id' => $video->id
        ]);

        Video::handleRelations($video, [
            'genres_id' => [$genresID[1], $genresID[2]]
        ]);

        $this->assertDatabaseMissing('genre_video', [
            'genre_id' => $genresID[0],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresID[1],
            'video_id' => $video->id
        ]);

        $this->assertDatabaseHas('genre_video', [
            'genre_id' => $genresID[2],
            'video_id' => $video->id
        ]);
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
        $video = factory(Video::class)->create();
        $video->delete();
        $this->assertNull(Video::find($video->id));

        $video->restore();
        $this->assertNotNull(Video::find($video->id));
    }
}


?>
