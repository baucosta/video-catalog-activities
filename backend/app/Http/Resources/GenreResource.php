<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
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
            'categories' => CategoryResource::collection(
                $this->whenLoaded('categories')
            )
        ];
        // return [
        //     'id' => $this->id,
        //     'name' => $this->name,
        //     'is_active' => $this->active ?? false,
        //     'categories' => CategoryResource::collection($this->categories),
        //     // 'categories' => CategoryResource::collection($this->whenLoaded($this->categories)),
        //     // 'categories' => $this->categories(),
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        //     'deleted_at' => $this->deleted_at
        // ];
    }
}
