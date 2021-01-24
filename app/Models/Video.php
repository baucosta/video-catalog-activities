<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes, \App\Models\Traits\Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    protected $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration'
    ];
    protected $dates = ['deleted_at'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer'
    ];

    public static function create(array $attributes = []) {
        try {
            \DB::beginTransaction();
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            \DB::commit();
            return $obj;
        }catch(\Exception $e) {
            if (isset($obj)) {

            }
            \DB::rollback();
            throw $e;

        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {

            }
            \DB::commit();

            return $saved;
        }catch(\Exception $e) {
            \DB::rollback();
            throw $e;

        }
    }

    public static function handleRelations(Video $video, array $attributes) {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }
        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }
    }

    public function categories() {
        return $this->belongsToMany(Category::class)->withoutTrashed();
    }

    public function genres() {
        return $this->belongsToMany(Genre::class)->withoutTrashed();
    }
}
