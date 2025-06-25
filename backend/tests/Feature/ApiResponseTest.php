<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class);

test('success response has correct structure', function () {
    $response = $this->get('/api/example/success');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'name' => 'example',
                'description' => 'This is a success response example'
            ]
        ]);
});

test('error response has correct structure', function () {
    $response = $this->get('/api/example/error');

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'code' => 400,
            'message' => 'Something went wrong',
            'data' => null
        ]);
});

test('paginate response has correct structure', function () {
    $response = $this->get('/api/example/paginate?page=1&per_page=5');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data',
            'meta' => [
                'total',
                'per_page',
                'current_page',
                'last_page'
            ]
        ]);

    expect($response->json('meta.per_page'))->toBe(5)
        ->and($response->json('meta.current_page'))->toBe(1)
        ->and($response->json('status'))->toBe('success')
        ->and($response->json('code'))->toBe(200);
});

test('collection response has correct structure', function () {
    $response = $this->get('/api/example/collection');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'code' => 200,
            'message' => 'Collection data retrieved successfully',
            'data' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
                ['id' => 3, 'name' => 'Item 3'],
            ]
        ]);
}); 