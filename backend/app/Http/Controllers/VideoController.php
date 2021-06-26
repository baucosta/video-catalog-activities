<?php

namespace App\Http\Controllers;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class VideoController extends BasicCrudController
{
    private $rules;

    public function __construct() {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y|min:1',
            'opened' => 'boolean',
            'rating' => 'required|in:'. implode(',', Video::RATING_LIST),
            'duration' => 'required|integer|min:1',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => [
                'required',
                'array',
                'exists:genres,id,deleted_at,NULL',
            ],
            'cast_members_id' => [
                'required',
                'array',
                'exists:cast_members,id,deleted_at,NULL',
            ],
            // 'video_file' => 'file|max:10240|mimes:mp4'
            'video_file' => 'mimetypes:video/mp4|max:'. Video::VIDEO_FILE_SIZE,
            'thumb_file' => 'image|max:'.Video::THUMB_FILE_SIZE,
            'banner_file' => 'image|max:'.Video::BANNER_FILE_SIZE,
            'trailer_file' => 'mimetypes:video/mp4|max:'. Video::TRAILER_FILE_SIZE,
        ];
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenreHasCategories($request);
        $validateData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validateData);
        $obj->refresh();

        $resource = $this->resource();
        return new $resource($obj);

    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleIfGenreHasCategories($request);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validateData);

        $resource = $this->resource();
        return new $resource($obj);

    }

    public function addRuleIfGenreHasCategories(Request $request) {
        $categoriesID = $request->get('categories_id');
        $categoriesID = is_array($categoriesID) ? $categoriesID : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule(
            $categoriesID
        );
    }

    public function show($id)
    {
        return parent::show($id);
    }



    protected function model() {
        return Video::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }

    protected function queryBuilder(): Builder
    {
        $action = \Route::getCurrentRoute()->getAction()['uses'];
        return parent::queryBuilder()->with([
            strpos($action, 'index') !== false
                ? 'genres'
                : 'genres.categories',
            'categories',
            'castMembers'
        ]);
    }
}
