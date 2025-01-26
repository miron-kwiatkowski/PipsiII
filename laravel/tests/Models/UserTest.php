<?php

namespace Tests\Models;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('casts method returns correct array', function () {
    $user = new User();
    $casts = $user->casts();

    expect($casts)
        ->toBeArray()
        ->toHaveKeys(['email_verified_at', 'password'])
        ->and($casts['email_verified_at'])->toBe('datetime')
        ->and($casts['password'])->toBe('hashed');
});

test('getAuthIdentifierName returns correct attribute name', function () {
    $user = new User();
    $authIdentifierName = $user->getAuthIdentifierName();

    expect($authIdentifierName)->toBe('email');
});

test('getAuthIdentifier returns correct identifier', function () {
    $email = 'test@example.com';
    request()->merge(['email' => $email]);

    $user = new User();
    $authIdentifier = $user->getAuthIdentifier();

    expect($authIdentifier)->toBe($email);
});

test('getAuthPassword returns the correct hashed password', function () {
    $password = 'example_password';
    request()->merge(['password' => $password]);

    $user = new User();
    $authPassword = $user->getAuthPassword();

    expect(Hash::check($password, $authPassword))->toBeTrue();
});

test('getName returns the correct name', function () {
    $name = 'John Doe';
    request()->merge(['name' => $name]);

    $user = new User();
    $userName = $user->getName();

    expect($userName)->toBe($name);
});

test('isAdmin returns true for admin users', function () {
    request()->merge(['isadmin' => true]);

    $user = new User();
    $isAdmin = $user->isAdmin();

    expect($isAdmin)->toBeTrue();
});

test('isAdmin returns false for non-admin users', function () {
    request()->merge(['isadmin' => false]);

    $user = new User();
    $isAdmin = $user->isAdmin();

    expect($isAdmin)->toBeFalse();
});
