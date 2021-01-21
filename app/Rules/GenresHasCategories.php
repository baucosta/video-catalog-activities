<?php

declare(strict_types=1);

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class GenresHasCategories implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    private $categoriesID;
    private $genresID;

    public function __construct(array $categoriesID)
    {
        $this->categoriesID = array_unique($categoriesID);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->genresID = array_unique($value);
        if(!count($this->genresID) || !count($this->categoriesID)) {
            return false;
        }

        $categoriesFound = [];
        foreach($this->genresID as $genreID) {
            $rows = $this->getRows($genreID);
            if(!$rows->count()) {
                return false;
            }
            array_push($categoriesFound, ...$rows->pluck('category_id')->toArray());
        }
        if(count($categoriesFound) !== count($this->categoriesID)) {
            return false;
        }
        return true;
    }

    protected function getRows($genreID): Collection {
        return \DB
            ::table('category_genre')
            ->where('genre_id', $genreID)
            ->whereIn('category_id', $this->categoriesID)
            ->get();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'A genre ID must be related at least to category ID.';
    }
}
