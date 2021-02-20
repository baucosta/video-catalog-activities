<?php

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class VideoSeeder extends Seeder
{
    private $allGenres;
    private $relations = [
        'genres_id' => [],
        'categories_id' => []
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = \Storage::getDriver()->getAdapter()->getPathPrefix();
        \File::deleteDirectory($dir, true);

        $self = $this;
        $this->allGenres = Genre::all();
        Model::reguard();

        factory(\App\Models\Video::class, 20)
            ->make()
            ->each(function(Video $video) use ($self) {
                $self->fetchRelations();
                \App\Models\Video::create(
                    array_merge(
                        $video->toArray(),
                        [
                            'thumb_file' => $self->getImageFile(),
                            'banner_file' => $self->getImageFile(),
                            'trailer_file' => $self->getVideoFile(),
                            'video_file' => $self->getVideoFile(),
                        ],
                        $this->relations
                    )
                );
            });
        Model::unguard();
    }

    public function fetchRelations() {
        $subGenres = $this->allGenres->random(5)->load('categories');
        $categoriesID = [];
        foreach($subGenres as $genre) {
            array_push($categoriesID, ...$genre->categories->pluck('id')->toArray());
        }
        $categoriesID = array_unique($categoriesID);
        $genresID = $subGenres->pluck('id')->toArray();
        $this->relations['categories_id'] = $categoriesID;
        $this->relations['genres_id'] = $genresID;
    }

    public function getImageFile() {
        return new UploadedFile(
            storage_path('faker/thumbs/Larvel Framework.png'),
            'Laravel Framework.png'
        );
    }

    public function getVideoFile() {
        return new UploadedFile(
            storage_path('faker/videos/01-Como vai funcionar os uploads.mp4'),
            '01-Como vai funcionar os uploads.mp4'
        );
    }
}
