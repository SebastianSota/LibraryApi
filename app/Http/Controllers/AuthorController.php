<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{
    public function index()
    {
        //$books = Book::all();
        $authors = Author::all();
        // return [
        //     "error" => false,
        //     "message" => "Successful",
        //     "data" => $books
        // ];

        return $this->getResponse200($authors);
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
        $author = new Author();
        $author->name = $request->name;
        $author->first_surname = $request->first_surname;
        $author->second_surname = $request->second_surname;
        $author->save();
        // $response["error"] = false;
        // $response["message"] = "Your book has been created!";
        // $response["data"] = $book;

        return $this->getResponse201("author", "created", $author);
    }

    public function update(Request $request, $id)
    {
        //$response = $this->response();
        $author = Author::find($id);

        DB::beginTransaction();
        try {

            if ($author) {
                $author->name = $request->name;
                $author->first_surname = $request->first_surname;
                $author->second_surname = $request->second_surname;
                $author->update();
                // $response["error"] = false;
                // $response["message"] = "Your book has been updated!";
                // $response["data"] = $book;
                $response = $this->getResponse201("author", "updated", $author);
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
        $author = Author::find($id);

        try {
            if ($author != null) {
                $response = $this->getResponse200($author);
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
        $author = Author::find($id);
        try {

            if ($author != null) {
                $author->books()->detach();
                $author->delete();
                $response = $this->getResponseDelete200("author");
            } else {
                //$response["message"] = "Not found";
                $response = $this->getResponse404();
            }
        } catch (Exception $e) {
            //$response["message"] = "Rollback transaction";
            //dd($e);
            $response = $this->getResponse500([$e->getMessage()]);
        }
        return $response;
    }
}
