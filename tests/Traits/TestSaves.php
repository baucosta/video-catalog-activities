<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Http\Controllers\GenreController;
use App\Models\Genre;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Request;
use Tests\Exceptions\TestException;

trait TestSaves {

    protected abstract function model();
    protected abstract function controller();
    protected abstract function routeStore();
    protected abstract function routeUpdate();

    /** @var TestResponse $response */
    protected function assertStore (array $sendData, array $testDatabase, array $testJsonData = null): TestResponse {
        $response = $this->json('POST', $this->routeStore(), $sendData);
        if ($response->status() !== 201) {
            throw new \Exception(
                "Response status must be 201, given {$response->status()}:\n{$response->content()}"
            );
        }

        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonResponseContent($response, $testDatabase, $testJsonData);

        return $response;
    }

    /** @var TestResponse $response */
    protected function assertUpdate (array $sendData, array $testDatabase, array $testJsonData = null): TestResponse {
        $response = $this->json('PUT', $this->routeUpdate(), $sendData);
        if ($response->status() !== 200) {
            throw new \Exception(
                "Response status must be 200, given {$response->status()}:\n{$response->content()}"
            );
        }

        $this->assertInDatabase($response, $testDatabase);
        $this->assertJsonResponseContent($response, $testDatabase, $testJsonData);

        return $response;
    }

    private function assertInDatabase(TestResponse$response, array $testDatabase) {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $testDatabase + ['id' => $response->json('id')]);
    }

    private function assertJsonResponseContent(TestResponse$response, array $testDatabase, array $testJsonData = null) {
        $testResponse = $testJsonData ?? $testDatabase;
        $response->assertJsonFragment($testResponse + ['id' => $response->json('id')]);
    }

    // protected function assertRollbackStore(array $sendData) {
    //     $controller = \Mockery::mock($this->controller())
    //         ->makePartial()
    //         ->shouldAllowMockingProtectedMethods();

    //     $controller->shouldReceive('validate')
    //         ->withAnyArgs()
    //         ->andReturn($sendData);

    //     $controller->shouldReceive('rulesStore')
    //         ->withAnyArgs()
    //         ->andReturn([]);

    //     $controller->shouldReceive('handleRelations')
    //         ->once()
    //         ->andThrow(new TestException());

    //     $request = \Mockery::mock(Request::class);
    //     $request->shouldReceive('get')
    //         ->withAnyArgs()
    //         ->andReturnNull();


    //     $hasError = false;
    //     try{
    //         $controller->store($request);
    //     } catch(TestException $exception) {
    //         $this->assertCount(1, $this->model()::all());
    //         $hasError = true;
    //     }

    //     $this->assertTrue($hasError);
    // }

    // protected function assertRollbackUpdate(array $sendData, $failData) {
    //     $controller = \Mockery::mock($this->controller())
    //         ->makePartial()
    //         ->shouldAllowMockingProtectedMethods();

    //     $controller->shouldReceive('findOrFail')
    //         ->withAnyArgs()
    //         ->andReturn($failData);

    //     $controller->shouldReceive('validate')
    //         ->withAnyArgs()
    //         ->andReturn($sendData);

    //     $controller->shouldReceive('rulesUpdate')
    //         ->withAnyArgs()
    //         ->andReturn([]);

    //     $controller->shouldReceive('handleRelations')
    //         ->once()
    //         ->andThrow(new TestException());

    //     $request = \Mockery::mock(Request::class);
    //         $request->shouldReceive('get')
    //             ->withAnyArgs()
    //             ->andReturnNull();

    //     $hasError = false;
    //     try{
    //         $controller->update($request, 1);
    //     } catch(TestException $exception) {
    //         $this->assertCount(1, $this->model()::all());
    //         $hasError = true;
    //     }

    //     $this->assertTrue($hasError);
    // }
}

?>
