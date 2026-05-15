<?php

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('can insert and query vectors with cosine distance', function () {
    DB::statement('CREATE TEMP TABLE vec_smoke (id serial, embedding vector(3))');
    DB::statement("INSERT INTO vec_smoke (embedding) VALUES ('[1,0,0]'), ('[0,1,0]')");

    $row = DB::selectOne(
        "SELECT id FROM vec_smoke ORDER BY embedding <=> '[1,0.1,0]' LIMIT 1"
    );

    expect($row->id)->toBe(1);
});
