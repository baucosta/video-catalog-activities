<?php

namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait TestResources {

    protected abstract function resource();
    protected abstract function model();

    public $assertQuantityPaginate = [
        'meta' => ['per_page' => 15]
    ];

    protected function assertResourceForFind(TestResponse $response) {
        $model = $this->model();
        $resource = $this->resource();

        $id = $response->json('data.id');
        $findById = (new $model)->find($id);
        $resource = new $resource($findById);
        $this->assertResource($response, $resource);
    }

    protected function assertResource(TestResponse $response, JsonResource $resource) {
        $response->assertJson($resource->response()->getData(true));
    }
}

?>
