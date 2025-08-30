<?php

it('returns ok on /api/health', function () {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertJson(['status' => 'ok']);
});
