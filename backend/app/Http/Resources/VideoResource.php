<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request) + [
            'categories' => CategoryResource::collection($this->categories),
            'genres' => GenreResource::collection($this->genres),
            'cast_members' => CastMemberResource::collection($this->castMembers),
            'video_file_url' => $this->video_file_url,
            'thumb_file_url' => $this->thumb_file_url,
            'banner_file_url' => $this->banner_file_url,
            'trailer_file_url' => $this->trailer_file_url
        ];
        // return [
        //     'id' => $this->id,
        //     'title' => $this->title,
        //     'description' => $this->description,
        //     'year_launched' => $this->year_launched,
        //     'opened' => $this->opened,
        //     'rating' => $this->rating,
        //     'duration' => $this->duration,
        //     'video_file' => $this->video_file,
        //     'thumb_file' => $this->thumb_file,
        //     'banner_file' => $this->banner_file,
        //     'trailer_file' => $this->trailer_file,
        //     'categories' => CategoryResource::collection($this->categories),
        //     'genres' => GenreResource::collection($this->genres),
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        //     'deleted_at' => $this->deleted_at
        // ];
    }
}
