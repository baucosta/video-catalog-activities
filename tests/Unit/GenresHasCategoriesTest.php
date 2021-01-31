<?php

namespace Tests\Unit;

use App\Rules\GenresHasCategoriesRule;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class GenresHasCategoriesTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCategoriesField() {
        $rule = new GenresHasCategoriesRule([1, 1, 2, 2]);
        $refectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $refectionClass->getProperty('categoriesID');
        $reflectionProperty->setAccessible(true);

        $categoriesID = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $categoriesID);
    }

    public function testGenresIdValue() {
        $rule = $this->createRuleMock([]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturnNull();
        $rule->passes('', [1, 1, 2, 2]);

        $refectionClass = new \ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $refectionClass->getProperty('genresID');
        $reflectionProperty->setAccessible(true);

        $genresID = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2], $genresID);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty() {
        $rule = $this->createRuleMock([1, 2]);
        $this->assertFalse($rule->passes('', []));

        $rule = $this->createRuleMock([1, 2]);
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty() {
        $rule = $this->createRuleMock([1]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect());
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres() {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1]
            ]));
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid() {
        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2]
            ]));
        $this->assertTrue($rule->passes('', [1]));

        $rule = $this->createRuleMock([1, 2]);
        $rule
            ->shouldReceive('getRows')
            ->withAnyArgs()
            ->andReturn(collect([
                ['category_id' => 1],
                ['category_id' => 2],
                ['category_id' => 1],
                ['category_id' => 2]
            ]));
        $this->assertTrue($rule->passes('', [1]));
    }

    protected function createRuleMock(array $categoriesID): MockInterface {
        return \Mockery::mock(GenresHasCategoriesRule::class, [$categoriesID])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
