<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingInterval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Book::all();
    }

    /**
     * Display the most recommended books
     */
    public function recommended() 
    {
        
        $result = Book::getMostRecommended(5);
        
        return response()->json($result);

    }


    /**
     * Add a book interval for the authenticated user
     */
    public function addInterval(Request $request) 
    {
        $bookId = $request->get('book_id');
        $book = Book::where('id', $bookId)->first();
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $request->validate([
            'start_page' => ["numeric", "min:1", "max:{$book->pages}", "required"],
            'end_page' => ["numeric", "min:1", "max:{$book->pages}", "required", "gte:start_page"],
        ]);

        $interval = new ReadingInterval();

        $interval->book_id = $bookId;
        $interval->user_id = $request->user()->id;
        $interval->start_page = $request->get('start_page');
        $interval->end_page = $request->get('end_page');

        $interval->save();

        return response()->json($interval);
    }

}
