<?php

use Tests\TestCase;

uses(TestCase::class);

it('homepage loads with correct SEO tags', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('TBA — Asystent dla firm usługowych | tbasystent.pl', escape: false);
    $response->assertSee('<h1>', escape: false);
    $response->assertSee('Twój biznes', escape: false);
    $response->assertSee('canonical', escape: false);
});

it('homepage links to register', function () {
    $response = $this->get('/');
    $response->assertSee('/admin/register', escape: false);
});

it('homepage is indexable (no noindex header)', function () {
    $response = $this->get('/');
    $response->assertHeaderMissing('X-Robots-Tag');
});
