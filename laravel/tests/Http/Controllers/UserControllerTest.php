<?php

namespace Tests\Http\Controllers;

use App\Models\users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;


uses(RefreshDatabase::class);

test('index method returns all users for admin', function () {
    $admin = users::factory()->create([
        '_token' => 'valid_admin_token',
        'IsAdmin' => true,
    ]);

    $users = users::factory(3)->create();

    $response = $this->getJson('/user', [
        'Authorization' => 'Bearer valid_admin_token',
    ]);

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
    expect($response->json('message'))->toBe('Retrieve successful');
});

test('index method returns unauthorized for non-admin', function () {
    $user = users::factory()->create([
        '_token' => 'valid_user_token',
        'IsAdmin' => false,
    ]);

    $response = $this->getJson('/user', [
        'Authorization' => 'Bearer valid_user_token',
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
    expect($response->json('data'))->toBe('null');
});

test('index method returns unauthorized with missing token', function () {
    $response = $this->getJson('/user');

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
    expect($response->json('data'))->toBe('null');
});

test('register method successfully registers a user with valid data', function () {
    $data = [
        'name' => 'Valid User',
        'email' => 'valid@example.com',
        'password' => 'validpassword',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(200);
    expect($response->json('message'))->toBe('Registration successful');
});

test('register method fails for duplicate email', function () {
    $existingUser = users::factory()->create([
        'Name' => 'Existing User',
        'Email' => 'duplicate@example.com',
        'Password' => Hash::make('password123'),
    ]);

    $data = [
        'name' => 'New User',
        'email' => 'duplicate@example.com',
        'password' => 'newpassword',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Email taken');
});

test('register method fails for invalid email', function () {
    $data = [
        'name' => 'Invalid Email User',
        'email' => 'not-an-email',
        'password' => 'validpassword',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Bad Request');
});

test('register method fails for short password', function () {
    $data = [
        'name' => 'Short Password User',
        'email' => 'shortpass@example.com',
        'password' => '123',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Bad Request');
});

test('register method fails for long password', function () {
    $data = [
        'name' => 'Long Password User',
        'email' => 'longpass@example.com',
        'password' => str_repeat('a', 41),
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Bad Request');
});

test('register method fails when name exceeds 40 character limit', function () {
    $data = [
        'name' => str_repeat('a', 41),
        'email' => 'longname@example.com',
        'password' => 'validpassword',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Bad Request');
});

test('register method fails when name is empty', function () {
    $data = [
        'name' => '',
        'email' => 'emptyname@example.com',
        'password' => 'validpassword',
    ];

    $response = $this->postJson('/register', $data);

    $response->assertStatus(400);
    expect($response->json('message'))->toBe('Bad Request');
});

test('redirectToGoogle method redirects to Google OAuth', function () {
    // Mock the Socialite driver
    Socialite::shouldReceive('driver->redirect')
        ->once()
        ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    $response = $this->get('/auth/redirect/google');

    $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
});

test('redirectToFacebook method redirects to Facebook OAuth', function () {
    // Mock the Socialite driver
    Socialite::shouldReceive('driver->redirect')
        ->once()
        ->andReturn(redirect('https://www.facebook.com/v11.0/dialog/oauth'));

    $response = $this->get('/auth/redirect/facebook');

    $response->assertRedirect('https://www.facebook.com/v11.0/dialog/oauth');
});

test('handleGoogleCallback registers a new user via Google OAuth', function () {
    // Mock Google user data
    $googleUser = (object)[
        'name' => 'Google User',
        'email' => 'googleuser@example.com',
    ];
    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get('/auth/callback/google');

    $response->assertStatus(200);
    expect($response->json('access_token'))->not->toBeNull();
    expect($response->json('message'))->toBe('Register successful');
});

test('handleGoogleCallback returns a new token for existing Google user', function () {
    // Create an existing user in the database
    $existingUser = users::factory()->create([
        'Name' => 'Existing Google User',
        'Email' => 'existinggoogleuser@example.com',
        'Password' => Hash::make('randompassword'),
        'Type' => 'g',
        '_token' => 'old_token',
    ]);

    // Mock existing Google user data
    $googleUser = (object)[
        'name' => 'Existing Google User',
        'email' => 'existinggoogleuser@example.com',
    ];
    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($googleUser);

    $response = $this->get('/auth/callback/google');

    $response->assertStatus(200);
    expect($response->json('access_token'))->not->toBeNull();
    expect($response->json('access_token'))->not->toBe('old_token');
    expect($response->json('message'))->toBe('Login successful');
});

test('handleFacebookCallback registers a new user via Facebook OAuth', function () {
    // Mock Facebook user data
    $facebookUser = (object)[
        'name' => 'Facebook User',
        'email' => 'facebookuser@example.com',
    ];
    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($facebookUser);

    $response = $this->get('/auth/callback/facebook');

    $response->assertStatus(200);
    expect($response->json('access_token'))->not->toBeNull();
    expect($response->json('message'))->toBe('Register successful');
});

test('handleFacebookCallback returns a new token for existing Facebook user', function () {
    // Create an existing user in the database
    $existingUser = users::factory()->create([
        'Name' => 'Existing Facebook User',
        'Email' => 'existingfacebookuser@example.com',
        'Password' => Hash::make('randompassword'),
        'Type' => 'fb',
        '_token' => 'old_token',
    ]);

    // Mock existing Facebook user data
    $facebookUser = (object)[
        'name' => 'Existing Facebook User',
        'email' => 'existingfacebookuser@example.com',
    ];
    Socialite::shouldReceive('driver->stateless->user')
        ->once()
        ->andReturn($facebookUser);

    $response = $this->get('/auth/callback/facebook');

    $response->assertStatus(200);
    expect($response->json('access_token'))->not->toBeNull();
    expect($response->json('access_token'))->not->toBe('old_token');
    expect($response->json('message'))->toBe('Login successful');
});

test('login method works with correct credentials', function () {
    $password = Hash::make('securepassword');
    $user = users::factory()->create([
        'Email' => 'validuser@example.com',
        'Password' => $password,
        'Type' => 'db',
        'IsBanned' => 0,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'validuser@example.com',
        'password' => 'securepassword',
    ]);

    $response->assertStatus(200);
    expect($response->json('message'))->toBe('Login successful');
    expect($response->json('access_token'))->not->toBeNull();
    expect($response->json('username'))->toBe($user->Name);
    expect($response->json('pfp'))->toBe($user->PfpNum);
});

test('login method fails with incorrect password', function () {
    $user = users::factory()->create([
        'Email' => 'validuser@example.com',
        'Password' => Hash::make('securepassword'),
        'Type' => 'db',
    ]);

    $response = $this->postJson('/login', [
        'email' => 'validuser@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
    expect($response->json('access_token'))->toBe('null');
});

test('login method fails when user is banned', function () {
    $user = users::factory()->create([
        'Email' => 'banneduser@example.com',
        'Password' => Hash::make('securepassword'),
        'Type' => 'db',
        'IsBanned' => 1,
    ]);

    $response = $this->postJson('/login', [
        'email' => 'banneduser@example.com',
        'password' => 'securepassword',
    ]);

    $response->assertStatus(403);
    expect($response->json('message'))->toBe('Forbidden');
    expect($response->json('access_token'))->toBe('null');
});

test('login method fails for non-existent email', function () {
    $response = $this->postJson('/login', [
        'email' => 'nonexistentuser@example.com',
        'password' => 'randompassword',
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
    expect($response->json('access_token'))->toBe('null');
});

test('adminmodify method modifies user attributes successfully for admin', function () {
    $admin = users::factory()->create([
        '_token' => 'valid_admin_token',
        'IsAdmin' => true,
    ]);

    $user = users::factory()->create([
        'CurrentGame' => 1,
        'IsAdmin' => false,
        'IsBanned' => false,
    ]);

    $response = $this->postJson('/adminmodify', [
        'access_token' => 'valid_admin_token',
        'userid' => $user->ID,
        'currentgame' => 2,
        'isadmin' => true,
        'isbanned' => true,
    ]);

    $response->assertStatus(200);
    expect($response->json('message'))->toBe('Modify successful');

    $user->refresh();
    expect($user->CurrentGame)->toBe(2);
    expect($user->IsAdmin)->toBe(true);
    expect($user->IsBanned)->toBe(true);
});

test('adminmodify method returns unauthorized for non-admin user', function () {
    $user = users::factory()->create([
        '_token' => 'valid_user_token',
        'IsAdmin' => false,
    ]);

    $response = $this->postJson('/adminmodify', [
        'access_token' => 'valid_user_token',
        'userid' => 1,
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
});

test('adminmodify method returns unauthorized if access_token is missing', function () {
    $response = $this->postJson('/adminmodify', [
        'userid' => 1,
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
});

test('adminmodify method returns unauthorized if userid is missing', function () {
    $admin = users::factory()->create([
        '_token' => 'valid_admin_token',
        'IsAdmin' => true,
    ]);

    $response = $this->postJson('/adminmodify', [
        'access_token' => 'valid_admin_token',
    ]);

    $response->assertStatus(401);
    expect($response->json('message'))->toBe('Unauthorized');
});

test('adminmodify method handles non-existent user ID gracefully', function () {
    $admin = users::factory()->create([
        '_token' => 'valid_admin_token',
        'IsAdmin' => true,
    ]);

    $response = $this->postJson('/adminmodify', [
        'access_token' => 'valid_admin_token',
        'userid' => 9999, // Non-existent ID
    ]);

    $response->assertStatus(200);
    expect($response->json('message'))->toBe('Modify successful');
});
