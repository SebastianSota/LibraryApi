<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReviews;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        //$books = Book::all();
        $books = Book::with('bookDownload', 'category', 'editorial', 'authors')->get();
        // return [
        //     "error" => false,
        //     "message" => "Successful",
        //     "data" => $books
        // ];

        return $this->getResponse200($books);
    }

    public function response()
    {
        return [
            "error" => true,
            "message" => "",
            "data" => []
        ];
    }

    public function store(Request $request)
    {
        //$response = $this->response();
        $isbn = trim($request->isbn);
        $existIsbn = Book::where('isbn', $isbn)->exists();
        if (!$existIsbn) {
            $book = new Book();
            $book->isbn = $isbn;
            $book->title = $request->title;
            $book->description = $request->description;
            $book->published_date = Carbon::now();
            $book->category_id = $request->category['id'];
            $book->editorial_id = $request->editorial['id'];
            $book->save();
            foreach ($request->authors as $item) {
                $book->authors()->attach($item);
            }
            // $response["error"] = false;
            // $response["message"] = "Your book has been created!";
            // $response["data"] = $book;

            $response = $this->getResponse201("book", "created", $book);
        } else {
            //$response["message"] = "ISBN duplicated!";
            $response = $this->getResponse400("ISBN duplicated!");
        }
        return $response;
    }

    public function update(Request $request, $id)
    {
        //$response = $this->response();
        $book = Book::find($id);

        DB::beginTransaction();
        try {

            if ($book) {
                $isbn = trim($request->isbn);
                $isbnOwner = Book::where('isbn', $isbn)->first();
                if (!$isbnOwner || $isbnOwner->id == $book->id) {
                    $book->isbn = $isbn;
                    $book->title = $request->title;
                    $book->description = $request->description;
                    $book->published_date = Carbon::now();
                    $book->category_id = $request->category['id'];
                    $book->editorial_id = $request->editorial['id'];
                    $book->update();
                    //Delete
                    foreach ($book->authors as $item) {
                        $book->authors()->detach($item);
                    }
                    //Add new authors
                    foreach ($request->authors as $item) {
                        $book->authors()->attach($item);
                    }
                    $book = Book::with('category', 'editorial', 'authors')->where('id', $id)->get();
                    // $response["error"] = false;
                    // $response["message"] = "Your book has been updated!";
                    // $response["data"] = $book;
                    $response = $this->getResponse201("book", "updated", $book);

                } else {
                    //$response["message"] = "ISBN duplicated!";
                    $response = $this->getResponse400("ISBN duplicated!");
                }
            } else {
                //$response["message"] = "Not found";
                $response = $this->getResponse404();
            }

            DB::commit();
        } catch (Exception $e) {
            //$response["message"] = "Rollback transaction";
            $response = $this->getResponse500([$e->getMessage()]);
            DB::rollBack();
        }
        return $response;
    }

    public function show($id)
    {
        $book = Book::find($id);

        try {

            if ($book != null) {
                $response = $this->getResponse200($book);
            } else {
                //$response["message"] = "Not found";
                $response = $this->getResponse404();
            }
        } catch (Exception $e) {
            //$response["message"] = "Rollback transaction";
            $response = $this->getResponse500([$e->getMessage()]);
        }

        return $response;
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        try {

            if ($book != null) {
                $book->authors()->detach();
                $book->delete();
                $response = $this->getResponseDelete200("book");
            } else {
                //$response["message"] = "Not found";
                $response = $this->getResponse404();
            }
        } catch (Exception $e) {
            //$response["message"] = "Rollback transaction";
            $response = $this->getResponse500([$e->getMessage()]);
        }
        return $response;
    }

    public function addBookReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $bookReview = new BookReviews();
                $bookReview->comment = $request->comment;
                $bookReview->book_id = $id;
                $bookReview->user_id = auth()->user()->id;
                $bookReview->edited = false;
                $bookReview->save();

                $bookReview = BookReviews::with("book", 'user')
                    ->where('user_id', auth()->user()->id)
                    ->where('book_id', $id)
                    ->first();

                DB::commit();

                return $this->getResponse201(
                    "book review",
                    "created",
                    $bookReview
                );
            } catch (Exception $err) {
                DB::rollBack();
                return $this->getResponse500([$err->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

    public function updateBookReview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required'
        ]);
        if (!$validator->fails()) {
            DB::beginTransaction();
            try {
                $bookReview = BookReviews::with("book", 'user')->where('id', $id)->first();
                if ($bookReview) {
                    if ($bookReview->user_id == auth()->user()->id) {
                        $bookReview->comment = trim($request->comment);
                        $bookReview->edited = true;
                        $bookReview->update();

                        $bookReview = BookReviews::with("book", 'user')->where('id', $id)->first();

                        DB::commit();

                        return $this->getResponse201(
                            "book review",
                            "updated",
                            $bookReview
                        );
                    } else {
                        return $this->getResponse403();
                    }
                } else {
                    return $this->getResponse404();
                }
            } catch (Exception $err) {
                DB::rollBack();
                return $this->getResponse500([$err->getMessage()]);
            }
        } else {
            return $this->getResponse500([$validator->errors()]);
        }
    }

}
