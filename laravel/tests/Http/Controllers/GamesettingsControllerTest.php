<?php

namespace Tests\Http\Controllers;

use App\Models\gamesettings;
use App\Models\users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\postJson;


uses(RefreshDatabase::class);

// Test unauthorized user without token
test('update fails for unauthorized user without token', function () {
    $response = postJson('/gamesettings/update', []);
    $response->assertStatus(401)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

// Test unauthorized user with invalid token
test('update fails for unauthorized user with invalid token', function () {
    $response = postJson('/gamesettings/update', ['access_token' => 'invalid_token']);
    $response->assertStatus(401)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

// Test with a valid token but non-admin user
test('update fails for non-admin user', function () {
    $user = users::factory()->create(['_token' => 'valid_token', 'IsAdmin' => false]);
    $response = postJson('/gamesettings/update', ['access_token' => $user->_token]);
    $response->assertStatus(401)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

// Test admin user successfully updates settings
test('update succeeds for admin user', function () {
    $admin = users::factory()->create(['_token' => 'admin_token', 'IsAdmin' => true]);
    $response = postJson('/gamesettings/update', [
        'access_token' => $admin->_token,
        'timereset' => '12:00:00',
        'mindistance' => 2,
        'maxdistance' => 15,
        'pointstoqualify' => 200,
        'leaderboarddays' => 30,
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'Settings saved',
    ]);

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => '12:00:00',
        'MinDistance' => 2,
        'MaxDistance' => 15,
        'PointsToQualify' => 200,
        'LeaderboardDays' => 30,
    ]);
});

// Test admin user updates settings with some missing fields
test('update succeeds with missing fields by copying from previous settings', function () {
    $admin = users::factory()->create(['_token' => 'admin_token', 'IsAdmin' => true]);

    $previousSettings = gamesettings::factory()->create([
        'TimeReset' => '12:00:00',
        'MinDistance' => 2,
        'MaxDistance' => 15,
        'PointsToQualify' => 200,
        'LeaderboardDays' => 14,
    ]);

    $response = postJson('/gamesettings/update', [
        'access_token' => $admin->_token,
        'timereset' => '18:00:00',
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'Settings saved',
    ]);

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => '18:00:00',
        'MinDistance' => $previousSettings->MinDistance,
        'MaxDistance' => $previousSettings->MaxDistance,
        'PointsToQualify' => $previousSettings->PointsToQualify,
        'LeaderboardDays' => $previousSettings->LeaderboardDays,
    ]);
});

// Test settings creation when no previous settings exist
test('update creates default settings if none exist', function () {
    $admin = users::factory()->create(['_token' => 'admin_token', 'IsAdmin' => true]);

    DB::table('gamesettings')->truncate();

    $response = postJson('/gamesettings/update', [
        'access_token' => $admin->_token,
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'Settings saved',
    ]);

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => '10:00:00',
        'MinDistance' => 1,
        'MaxDistance' => 10,
        'PointsToQualify' => 100,
        'LeaderboardDays' => 10,
    ]);
});

// Test default method creates settings when no entries exist
test('default creates settings when table is empty', function () {
    DB::table('gamesettings')->truncate();

    $controller = app(\App\Http\Controllers\GamesettingsController::class);
    $controller->default();

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => '10:00:00',
        'MinDistance' => 1,
        'MaxDistance' => 10,
        'PointsToQualify' => 100,
        'LeaderboardDays' => 10,
    ]);
});

// Test default does not overwrite existing settings
test('default does not overwrite existing settings', function () {
    $existingSettings = gamesettings::factory()->create([
        'TimeReset' => '12:00:00',
        'MinDistance' => 2,
        'MaxDistance' => 15,
        'PointsToQualify' => 200,
        'LeaderboardDays' => 30,
    ]);

    $controller = app(\App\Http\Controllers\GamesettingsController::class);
    $controller->default();

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => $existingSettings->TimeReset,
        'MinDistance' => $existingSettings->MinDistance,
        'MaxDistance' => $existingSettings->MaxDistance,
        'PointsToQualify' => $existingSettings->PointsToQualify,
        'LeaderboardDays' => $existingSettings->LeaderboardDays,
    ]);
});

// Test unauthorized user without token
test('get fails for unauthorized user without token', function () {
    $response = postJson('/gamesettings/get', []);
    $response->assertStatus(400)
        ->assertJson([
            'data' => 'null',
            'message' => 'Bad Request',
        ]);
});

// Test unauthorized user with invalid token
test('get fails for unauthorized user with invalid token', function () {
    $response = postJson('/gamesettings/get', ['access_token' => 'invalid_token']);
    $response->assertStatus(401)
        ->assertJson([
            'data' => 'null',
            'message' => 'Unauthorized',
        ]);
});

// Test admin user fetches settings successfully
test('get succeeds for admin user', function () {
    $admin = users::factory()->create(['_token' => 'admin_token', 'IsAdmin' => true]);

    $settings = gamesettings::factory()->create([
        'TimeReset' => '12:00:00',
        'MinDistance' => 2,
        'MaxDistance' => 15,
        'PointsToQualify' => 200,
        'LeaderboardDays' => 30,
    ]);

    $response = postJson('/gamesettings/get', [
        'access_token' => $admin->_token,
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'Data fetched',
        'data' => [
            'TimeReset' => $settings->TimeReset,
            'MinDistance' => $settings->MinDistance,
            'MaxDistance' => $settings->MaxDistance,
            'PointsToQualify' => $settings->PointsToQualify,
            'LeaderboardDays' => $settings->LeaderboardDays,
        ],
    ]);
});

// Test settings creation and fetch when no previous settings exist
test('get creates and fetches default settings if none exist', function () {
    $admin = users::factory()->create(['_token' => 'admin_token', 'IsAdmin' => true]);

    DB::table('gamesettings')->truncate();

    $response = postJson('/gamesettings/get', [
        'access_token' => $admin->_token,
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'Data fetched',
        'data' => [
            'TimeReset' => '10:00:00',
            'MinDistance' => 1,
            'MaxDistance' => 10,
            'PointsToQualify' => 100,
            'LeaderboardDays' => 10,
        ],
    ]);

    $this->assertDatabaseHas('gamesettings', [
        'TimeReset' => '10:00:00',
        'MinDistance' => 1,
        'MaxDistance' => 10,
        'PointsToQualify' => 100,
        'LeaderboardDays' => 10,
    ]);
});
