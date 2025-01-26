<?php

namespace Tests\Http\Controllers;

use App\Models\puzzles;
use App\Models\users;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index returns all puzzles for authorized admin', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    puzzles::factory()->count(3)->create();

    $response = $this->getJson('/puzzles', ['access_token' => 'valid_token']);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'IMGSource', 'Xvalue', 'Yvalue', 'IMGDesc', 'Difficulty']
            ],
            'message',
        ])->assertJson(['message' => 'Retrieve successful']);
});

test('index returns unauthorized if access_token is missing', function () {
    $response = $this->getJson('/puzzles');

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('index returns unauthorized if access_token is invalid', function () {
    $user = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);

    $response = $this->getJson('/puzzles', ['access_token' => 'invalid_token']);

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('index returns unauthorized for non-admin users', function () {
    $user = users::factory()->create(['IsAdmin' => 0, '_token' => 'valid_token']);

    $response = $this->getJson('/puzzles', ['access_token' => 'valid_token']);

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('create successfully creates a puzzle for authorized admin', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    $payload = [
        'access_token' => 'valid_token',
        'imagesource' => 'test_image.png',
        'xvalue' => 10,
        'yvalue' => 20,
        'description' => 'A test puzzle',
        'difficulty' => 2,
    ];

    $response = $this->postJson('/puzzles/create', $payload);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'IMGSource', 'Xvalue', 'Yvalue', 'IMGDesc', 'Difficulty'],
            'message',
        ])->assertJson(['message' => 'Puzzle created']);
});

test('create returns unauthorized if access_token is missing', function () {
    $payload = [
        'imagesource' => 'test_image.png',
        'xvalue' => 10,
        'yvalue' => 20,
        'description' => 'A test puzzle',
        'difficulty' => 2,
    ];

    $response = $this->postJson('/puzzles/create', $payload);

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('create returns unauthorized if access_token is invalid', function () {
    $user = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    $payload = [
        'access_token' => 'invalid_token',
        'imagesource' => 'test_image.png',
        'xvalue' => 10,
        'yvalue' => 20,
        'description' => 'A test puzzle',
        'difficulty' => 2,
    ];

    $response = $this->postJson('/puzzles/create', $payload);

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('create returns unauthorized for non-admin users', function () {
    $user = users::factory()->create(['IsAdmin' => 0, '_token' => 'valid_token']);
    $payload = [
        'access_token' => 'valid_token',
        'imagesource' => 'test_image.png',
        'xvalue' => 10,
        'yvalue' => 20,
        'description' => 'A test puzzle',
        'difficulty' => 2,
    ];

    $response = $this->postJson('/puzzles/create', $payload);

    $response->assertStatus(401)
        ->assertJson(['data' => 'null', 'message' => 'Unauthorized']);
});

test('create returns error if imagesource is missing', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    $payload = [
        'access_token' => 'valid_token',
        'xvalue' => 10,
        'yvalue' => 20,
        'description' => 'A test puzzle',
        'difficulty' => 2,
    ];

    $response = $this->postJson('/puzzles/create', $payload);

    $response->assertStatus(404)
        ->assertJson(['data' => 'null', 'message' => 'No image source']);
});

test('delete successfully deletes a puzzle for authorized admin', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    $puzzle = puzzles::factory()->create();

    $response = $this->deleteJson('/puzzles/delete', [
        'access_token' => 'valid_token',
        'puzzleid' => $puzzle->ID,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Puzzle deleted']);
});

test('delete returns unauthorized if access_token is missing', function () {
    $response = $this->deleteJson('/puzzles/delete', ['puzzleid' => 1]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('delete returns unauthorized if access_token is invalid', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);

    $response = $this->deleteJson('/puzzles/delete', [
        'access_token' => 'invalid_token',
        'puzzleid' => 1,
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('delete returns unauthorized for non-admin users', function () {
    $user = users::factory()->create(['IsAdmin' => 0, '_token' => 'valid_token']);
    $puzzle = puzzles::factory()->create();

    $response = $this->deleteJson('/puzzles/delete', [
        'access_token' => 'valid_token',
        'puzzleid' => $puzzle->ID,
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('delete returns not found if puzzle does not exist', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);

    $response = $this->deleteJson('/puzzles/delete', [
        'access_token' => 'valid_token',
        'puzzleid' => 9999,
    ]);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Puzzle not found']);
});

test('edit successfully updates a puzzle for authorized admin', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);
    $puzzle = puzzles::factory()->create([
        'Xvalue' => 10,
        'Yvalue' => 20,
        'IMGDesc' => 'Old description',
        'Difficulty' => 2,
    ]);
    $payload = [
        'access_token' => 'valid_token',
        'puzzleid' => $puzzle->ID,
        'xvalue' => 50,
        'yvalue' => 100,
        'description' => 'New description',
        'difficulty' => 3,
    ];

    $response = $this->patchJson('/puzzles/edit', $payload);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Modify successful']);

    $this->assertDatabaseHas('puzzles', [
        'ID' => $puzzle->ID,
        'Xvalue' => 50,
        'Yvalue' => 100,
        'IMGDesc' => 'New description',
        'Difficulty' => 3,
    ]);
});

test('edit returns unauthorized if access_token is missing', function () {
    $payload = [
        'puzzleid' => 1,
        'xvalue' => 50,
        'yvalue' => 100,
        'description' => 'New description',
        'difficulty' => 3,
    ];

    $response = $this->patchJson('/puzzles/edit', $payload);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('edit returns unauthorized if access_token is invalid', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);

    $payload = [
        'access_token' => 'invalid_token',
        'puzzleid' => 1,
        'xvalue' => 50,
        'yvalue' => 100,
        'description' => 'New description',
        'difficulty' => 3,
    ];

    $response = $this->patchJson('/puzzles/edit', $payload);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('edit returns unauthorized for non-admin users', function () {
    $user = users::factory()->create(['IsAdmin' => 0, '_token' => 'valid_token']);
    $payload = [
        'access_token' => 'valid_token',
        'puzzleid' => 1,
        'xvalue' => 50,
        'yvalue' => 100,
        'description' => 'New description',
        'difficulty' => 3,
    ];

    $response = $this->patchJson('/puzzles/edit', $payload);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized']);
});

test('edit returns not found if puzzle does not exist', function () {
    $admin = users::factory()->create(['IsAdmin' => 1, '_token' => 'valid_token']);

    $payload = [
        'access_token' => 'valid_token',
        'puzzleid' => 9999,
        'xvalue' => 50,
        'yvalue' => 100,
        'description' => 'New description',
        'difficulty' => 3,
    ];

    $response = $this->patchJson('/puzzles/edit', $payload);

    $response->assertStatus(404)
        ->assertJson(['message' => 'Puzzle not found']);
});
