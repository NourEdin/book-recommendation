<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingInterval;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;

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
    public function addInterval(Request $request, Logger $logger) 
    {
        $bookId = $request->get('book_id');
        $book = Book::where('id', $bookId)->first();
        $user = request()->user();
        
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $request->validate([
            'start_page' => ["numeric", "min:1", "max:{$book->pages}", "required"],
            'end_page' => ["numeric", "min:1", "max:{$book->pages}", "required", "gte:start_page"],
        ]);

        $interval = new ReadingInterval();

        $interval->book_id = $bookId;
        $interval->user_id = $user->id;
        $interval->start_page = $request->get('start_page');
        $interval->end_page = $request->get('end_page');

        $interval->save();

        //Send an SMS to thank the user
        //TODO: Do this in a job queue
        if ($user->phone) {
            $message = "Thank you for your valuable feedback";
            $env = env('APP_ENV', 'local');
            if ($env == 'production') {
                $url = env("SMS_API_PROD");
            } else {
                $url = env("SMS_API_DEV");
            }

            $response = Http::post($url, [
                'phone' => $user->phone,
                'message' => $message,
            ]);

            if (!$response->successful()) {
                $logger->error('Could not send an SMS', ['user' => $user, 'url' => $url, 'response' => $response]);
            }
        }
        

        return response()->json($interval);
    }

}
