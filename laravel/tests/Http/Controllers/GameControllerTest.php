<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\GameController;
use App\Models\guesses;
use App\Models\puzzles;
use App\Models\users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that the reset method assigns an unresolved puzzle
     * to each user who has not solved all puzzles.
     */
    public function testResetAssignsUnresolvedPuzzleToUsers()
    {
        // Create mock puzzles
        $puzzle1 = puzzles::factory()->create();
        $puzzle2 = puzzles::factory()->create();

        // Create mock users
        $user1 = users::factory()->create();
        $user2 = users::factory()->create();

        // Create guesses for one user
        guesses::factory()->create(['UserId' => $user1->ID, 'PuzzleId' => $puzzle1->ID, 'DidWin' => true]);

        // Call the reset method
        GameController::reset();

        // User1 should be assigned another puzzle (not already solved)
        $this->assertNotEquals($user1->CurrentGame, $puzzle1->ID);

        // User2 should be assigned any puzzle since no puzzles have been solved
        $this->assertNotNull($user2->CurrentGame);
    }

    /**
     * Tests that reset method clears the game for users
     * who have solved all puzzles.
     */
    public function testResetClearsCurrentGameForUsersWithoutRemainingPuzzles()
    {
        // Create mock puzzles
        $puzzle1 = puzzles::factory()->create();

        // Create mock user
        $user = users::factory()->create();

        // Create guesses such that the user has solved all puzzles
        guesses::factory()->create(['UserId' => $user->ID, 'PuzzleId' => $puzzle1->ID, 'DidWin' => true]);

        // Call the reset method
        GameController::reset();

        // Assert the user's CurrentGame is cleared
        $this->assertEquals(0, $user->CurrentGame);
    }

    /**
     * Tests that reset method assigns a random puzzle when multiple options are available.
     */
    public function testResetAssignsRandomPuzzle()
    {
        // Create mock puzzles
        $puzzle1 = puzzles::factory()->create();
        $puzzle2 = puzzles::factory()->create();

        // Create mock user
        $user = users::factory()->create();

        // Call the reset method multiple times
        GameController::reset();
        $firstPuzzle = $user->refresh()->CurrentGame;

        GameController::reset();
        $secondPuzzle = $user->refresh()->CurrentGame;

        // Assert the user is assigned one of the available puzzles
        $this->assertContains($firstPuzzle, [$puzzle1->ID, $puzzle2->ID]);
        $this->assertContains($secondPuzzle, [$puzzle1->ID, $puzzle2->ID]);
    }

    /**
     * Tests that reset works correctly when no puzzles are available.
     */
    public function testResetDoesNothingWithNoPuzzles()
    {
        // Create a mock user
        $user = users::factory()->create();

        // Call the reset method
        GameController::reset();

        // Assert the CurrentGame remains null or zero
        $this->assertEquals(0, $user->CurrentGame);
    }

    /**
     * Tests that the get method returns user stats if a puzzle has been solved today.
     */
    public function testGetReturnsUserStatsIfPuzzleSolvedToday()
    {
        // Create mock user and puzzle
        $user = users::factory()->create(['_token' => 'test_token']);
        $puzzle = puzzles::factory()->create();

        // Create mock guess for today
        guesses::factory()->create([
            'UserId' => $user->ID,
            'PuzzleId' => $puzzle->ID,
            'Date' => date('Y/m/d'),
            'Points' => 1000,
            'Time' => 60,
            'DidWin' => true,
            'IMGSource' => 'example/image/path.jpg',
        ]);

        // Send request
        $response = $this->postJson(route('game.get'), ['access_token' => 'test_token']);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Stats displayed',
                'data' => [
                    'source' => 'example/image/path.jpg',
                    'points' => 1000,
                    'time' => 60,
                    'didwin' => true,
                ],
            ]);
    }

    /**
     * Tests that the get method returns the current puzzle if available.
     */
    public function testGetReturnsCurrentPuzzleIfAvailable()
    {
        // Create mock user and puzzle
        $user = users::factory()->create(['_token' => 'test_token']);
        $puzzle = puzzles::factory()->create([
            'IMGSource' => 'example/image/path.jpg',
            'Difficulty' => 'medium',
        ]);

        // Assign current game to user
        $user->update(['CurrentGame' => $puzzle->ID]);

        // Send request
        $response = $this->postJson(route('game.get'), ['access_token' => 'test_token']);

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Stats displayed',
                'source' => 'example/image/path.jpg',
                'difficulty' => 'medium',
            ]);
    }

    /**
     * Tests that the get method returns unauthorized for invalid tokens.
     */
    public function testGetReturnsUnauthorizedForInvalidToken()
    {
        // Send request with invalid token
        $response = $this->postJson(route('game.get'), ['access_token' => 'invalid_token']);

        // Assert response
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
                'data' => 'null',
            ]);
    }

    /**
     * Tests that the get method returns puzzle not found if no current puzzle is assigned.
     */
    public function testGetReturnsPuzzleNotFoundIfNoCurrentPuzzle()
    {
        // Create mock user
        $user = users::factory()->create(['_token' => 'test_token']);

        // Send request
        $response = $this->postJson(route('game.get'), ['access_token' => 'test_token']);

        // Assert response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Puzzle not found',
                'data' => 'null',
            ]);
    }

    /**
     * Tests the guess method with valid input and checks point assignment.
     */
    public function testGuessWithValidInput()
    {
        $user = users::factory()->create(['_token' => 'valid_token']);
        $puzzle = puzzles::factory()->create(['XValue' => 15, 'YValue' => 20]);

        $user->update(['CurrentGame' => $puzzle->ID]);
        $rules = gamesettings::factory()->create(['MinDistance' => 5, 'MaxDistance' => 20, 'PointsToQualify' => 3000]);

        $response = $this->postJson(route('game.guess'), [
            'access_token' => 'valid_token',
            'xvalue' => 10,
            'yvalue' => 15,
            'time' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'UserId',
                    'PuzzleId',
                    'Points',
                    'Time',
                    'Date',
                    'DidWin',
                ],
                'message',
            ]);

        $this->assertEquals($user->ID, $response->json('data.UserId'));
        $this->assertEquals($puzzle->ID, $response->json('data.PuzzleId'));
        $this->assertGreaterThan(0, $response->json('data.Points'));
    }

    /**
     * Tests the guess method with an invalid token.
     */
    public function testGuessWithInvalidToken()
    {
        $response = $this->postJson(route('game.guess'), [
            'access_token' => 'invalid_token',
            'xvalue' => 10,
            'yvalue' => 15,
            'time' => 20,
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
                'data' => 'null',
            ]);
    }

    /**
     * Tests the guess method when distance exceeds the maximum allowed distance.
     */
    public function testGuessWithDistanceExceedingMaxAllowed()
    {
        $user = users::factory()->create(['_token' => 'valid_token']);
        $puzzle = puzzles::factory()->create(['XValue' => 10, 'YValue' => 10]);

        $user->update(['CurrentGame' => $puzzle->ID]);
        gamesettings::factory()->create(['MinDistance' => 5, 'MaxDistance' => 15]);

        $response = $this->postJson(route('game.guess'), [
            'access_token' => 'valid_token',
            'xvalue' => 50, // Exceeds max distance
            'yvalue' => 50, // Exceeds max distance
            'time' => 20,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Guess saved',
                'data' => [
                    'Points' => 0,
                    'DidWin' => false,
                ],
            ]);
    }

    /**
     * Tests the guess method when the assigned puzzle does not exist.
     */
    public function testGuessWithNonExistentPuzzle()
    {
        $user = users::factory()->create(['_token' => 'valid_token']);
        $user->update(['CurrentGame' => 999]); // Puzzle ID does not exist

        $response = $this->postJson(route('game.guess'), [
            'access_token' => 'valid_token',
            'xvalue' => 10,
            'yvalue' => 10,
            'time' => 20,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Data not found',
                'data' => 'null',
            ]);
    }

    /**
     * Tests the guess method for missing required input.
     */
    public function testGuessWithMissingInput()
    {
        $user = users::factory()->create(['_token' => 'valid_token']);
        $puzzle = puzzles::factory()->create();

        $user->update(['CurrentGame' => $puzzle->ID]);

        $response = $this->postJson(route('game.guess'), [
            'access_token' => 'valid_token',
            'time' => 20, // Missing xvalue and yvalue
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Bad request',
                'data' => 'null',
            ]);
    }
}
