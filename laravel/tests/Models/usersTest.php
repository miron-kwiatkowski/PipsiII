<?php

namespace Tests\Models;

use App\Models\users;

test('getAuthIdentifier returns the email of the user', function () {
    $user = new users(['Email' => 'test@example.com']);
    expect($user->getAuthIdentifier())->toBe('test@example.com');
});

test('getAuthIdentifier returns null when email is not set', function () {
    $user = new users();
    expect($user->getAuthIdentifier())->toBe(null);
});

test('getAuthPassword returns the password of the user', function () {
    $user = new users(['Password' => 'hashed_password']);
    expect($user->getAuthPassword())->toBe('hashed_password');
});

test('getAuthPassword returns null when password is not set', function () {
    $user = new users();
    expect($user->getAuthPassword())->toBe(null);
});
