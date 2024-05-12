<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * Gets the most recommened books based on how many unique pages were read
     */
    public static function getMostRecommended($total = 5) {
        $prevEnd = $bookId = 0;
        $bookPages = [];

        //Query in chunks to avoid memory leakage
        ReadingInterval::
            orderBy('book_id', 'asc')
            ->orderBy('start_page', 'asc')
            ->orderBy('end_page', 'asc')
            ->chunk(100, function (Collection $intervals) use ($prevEnd, $bookId, &$bookPages) {
                foreach ($intervals as $interval) {
                    $newBookId = $interval->book_id;
                    $newStart = $interval->start_page;
                    $newEnd = $interval->end_page;

                    //If reached a new book, reset counter and ranges
                    if ($newBookId != $bookId) {
                        $prevEnd = 0;
                        $bookId = $newBookId;
                        $bookPages[$bookId] = 0;
                    }

                    if ($newStart > $prevEnd) {
                        //This interval and the previous ones aren't overlapped
                        $bookPages[$bookId] = self::appendRange($bookPages[$bookId], $newStart, $newEnd);
                        $prevEnd = $newEnd;
                    } else { //The two intervals are overlapped
                        if ($newEnd <= $prevEnd) { 
                            //The new interval is completely included in the previous one -> Ignore it
                            continue;
                        } else {
                            //This interval starts within the previous one, and ends after it. Can be visually represented as follows:
                            // Prev interval: |-----|
                            // New Interval:    |---------|
                            //                      ^-----^ Append this part only to the previous interval.
                            // Note that $newStart cannot be less than "the previous start" because the intervals are ordered by start,end ascending
                            $bookPages[$bookId] = self::appendRange($bookPages[$bookId], $prevEnd + 1, $newEnd);
                        }
                    }
                }

        });


        //Find the most recommended 5 books
        arsort($bookPages); //Sorts the array based on values (pages) in descending order
        $books = [];
        $count = 0;
        foreach ($bookPages as $bookId => $pages) {
            if ($count == $total) {
                break;
            }

            $book = Book::where('id', $bookId)->first();
            if ($book) {
                $books[] = [
                    'book_id' => $bookId,
                    'book_name' => $book->name,
                    'num_of_read_pages' => $pages,
                ];
                $count++;
            }
        }

        return $books;
    }

    /**
     * Used in recommended endpoint. Accumulates book pages assuming $start and $end are included in the range
     */
    private static function appendRange($total, $start, $end) {
        return $total + $end - $start + 1;
    }
}
