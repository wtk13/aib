<?php

use Tests\TestCase;

uses(TestCase::class);

it('homepage returns 200', function () {
    $this->get('/')->assertOk();
});

it('homepage contains the hero headline', function () {
    $this->get('/')
        ->assertSee('CRM i AI asystent')
        ->assertSee('firm usługowych', false);
});

it('homepage contains the primary CTA linking to registration', function () {
    $this->get('/')
        ->assertSee('Wypróbuj za darmo')
        ->assertSee('/admin/register', false);
});

it('homepage contains features section', function () {
    $this->get('/')
        ->assertSee('Wyceny w minutę')
        ->assertSee('Plan dnia bez chaosu');
});

it('homepage contains pricing section', function () {
    $this->get('/')
        ->assertSee('0 zł')
        ->assertSee('Zacznij teraz — za darmo');
});

it('homepage does not expose Filament panel', function () {
    $this->get('/')->assertDontSee('/admin/login', false);
});
