<?php

namespace App\ModelFilters;

class CastMemberFilter extends DefaultModelFilter
{

    protected $sortable = ['name', 'type', 'created_at'];

    public function search($search) {
        $this->where('name', 'LIKE', "%$search%");
    }
}
