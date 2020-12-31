<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\CastMember;
use Faker\Generator as Faker;

$factory->define(CastMember::class, function (Faker $faker) {
    // $types = [CastMember::TYPE_DIRECTOR, CastMember::TYPE_ACTOR];

    return [
        'name' => $faker->lastName,
        'type' => rand(1, 10) % 2 == 0 ? 1: 2
        // 'type' => $types[array_rand($types)]
    ];
});
