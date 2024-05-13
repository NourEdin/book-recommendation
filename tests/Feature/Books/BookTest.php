<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingInterval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_books(): void
    {
        $books = Book::factory(2)->create();

        $response = $this->get('/books');

        $response
            ->assertStatus(200)
            ->assertJsonCount(count($books))
        ;

    }

    public function test_adding_reading_interval() {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        
        $startPage = rand(1, $book->pages);
        $endPage = rand($startPage, $book->pages);
        
        $response = $this->add_interval($user, $book->id, $startPage, $endPage);

        $response
            ->assertStatus(201)
            ->assertJson(function (AssertableJson $json) use($user, $startPage, $endPage, $book) {
                    return $json->where('id', 1)
                    ->where('book_id', $book->id)
                    ->where('user_id', $user->id)
                    ->where('start_page', $startPage)
                    ->where('end_page', $endPage)
                    ->etc();
                }
            )
        ;
        
    }
    public function test_recommended_books_number() {
        $books = Book::factory(6)->create();
        $users = User::factory(3)->create();

        //Add random intervals
        for ($i=0; $i < 10; $i++) { 
            $user = $users[rand(1, count($users)-1)];
            $book = $books[rand(1, count($books)-1)];
            $start = rand(1, $book->pages);
            $end = rand($start, $book->pages);

            $this->add_interval($user, $book->id, $start, $end);
        }

        $response = $this->get('/books/recommended')->json();
        assertTrue(count($response) <= 5);
    
    }
    public function test_recommended_books_example() {
        //Example case
        $book1 = Book::factory()->create(['pages' => 100]);
        $book2 = Book::factory()->create(['pages' => 200]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $intervals = [
            [
                "user" => $user1,
                "book" => $book1,
                "start" => 10,
                "end" => 30,
            ],
            [
                "user" => $user2,
                "book" => $book1,
                "start" => 2,
                "end" => 25,
            ],
            [
                "user" => $user1,
                "book" => $book2,
                "start" => 40,
                "end" => 50,
            ],
            [
                "user" => $user3,
                "book" => $book2,
                "start" => 1,
                "end" => 10,
            ],
        ];

        //Insert intervals
        foreach ($intervals as $interval) {
            $this->add_interval($interval['user'], $interval['book']->id, $interval['start'], $interval['end']);
        }

        //Test result
        $response = $this->get('/books/recommended');
        $result = $response->json();

        assertEquals($book1->id, $result[0]['book_id']);
        assertEquals(29, $result[0]['num_of_read_pages']);

        assertEquals($book2->id, $result[1]['book_id']);
        assertEquals(21, $result[1]['num_of_read_pages']);
        
    }

    private function add_interval(User $user, $book_id, $start, $end): TestResponse {

        return $this->actingAs($user)->post('/interval', [
            "book_id" => $book_id,
            "start_page" => $start,
            "end_page" => $end
        ]);
    }
}
