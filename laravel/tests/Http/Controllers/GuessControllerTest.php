<?php

namespace Tests\Http\Controllers;

use App\Models\guesses;
use App\Models\users;
use Illuminate\Http\Response;
use function Pest\Laravel\postJson;

test('it returns 401 unauthorized if access token is missing', function () {
    $response = postJson('/api/stats', [
        'id' => 1,
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

test('it returns 401 unauthorized if user is not admin', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => false]);

    $response = postJson('/api/stats', [
        'access_token' => 'valid-token',
        'id' => 1,
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

test('it returns 404 not found if puzzle does not exist', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => true]);

    $response = postJson('/api/stats', [
        'access_token' => 'valid-token',
        'id' => 999,
    ]);

    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => 'null',
            'message' => 'Puzzle not found',
        ]);
});

test('it returns statistics if admin and puzzle exist', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => true]);

    guesses::factory()->createMany([
        ['PuzzleId' => 1, 'DidWin' => 1, 'Points' => 50, 'Time' => 30, 'Date' => now()->subMinutes(10)],
        ['PuzzleId' => 1, 'DidWin' => 0, 'Points' => 0, 'Time' => 45, 'Date' => now()->subMinutes(20)],
        ['PuzzleId' => 1, 'DidWin' => 1, 'Points' => 70, 'Time' => 25, 'Date' => now()->subMinutes(5)],
    ]);

    $response = postJson('/api/stats', [
        'access_token' => 'valid-token',
        'id' => 1,
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonFragment([
            'message' => 'Stats fetched',
        ]);

    $responseData = $response->json('data');
    $stats = json_decode($responseData, true);

    expect($stats['Winrate'])->toBe('66.67%');
    expect($stats['Last guess'])->not->toBe(null);
    expect($stats['Points average'])->toBe(60);
    expect($stats['Time avg'])->toBe(27.5);
});

test('it returns 401 unauthorized if access token is missing for scoreboard', function () {
    $response = postJson('/api/scoreboard');

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'data' => null,
            'message' => 'Unauthorized',
        ]);
});

test('it returns 401 unauthorized if user is not admin for scoreboard', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => false]);

    $response = postJson('/api/scoreboard', [
        'access_token' => 'valid-token',
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'data' => null,
            'message' => 'Unauthorized',
        ]);
});

test('it returns empty scoreboard if no guesses data exists', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => true]);

    $response = postJson('/api/scoreboard', [
        'access_token' => 'valid-token',
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [],
        ]);
});

test('it returns valid scoreboard data if guesses exist', function () {
    $user = users::factory()->create(['_token' => 'valid-token', 'IsAdmin' => true]);

    guesses::factory()->createMany([
        ['PuzzleId' => 1, 'UserId' => $user->id, 'Points' => 100, 'Date' => now()->subDay()],
        ['PuzzleId' => 2, 'UserId' => $user->id, 'Points' => 200, 'Date' => now()->subDay()],
    ]);

    $response = postJson('/api/scoreboard', [
        'access_token' => 'valid-token',
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'data' => [
                '*' => ['Name', 'Points'],
            ],
        ]);
});
