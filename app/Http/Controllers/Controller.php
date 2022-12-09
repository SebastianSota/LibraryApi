<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getResponse200($data)
    {
        return response()->json([
            'message' => 'Successful operation',
            'data' => $data,
        ], 200);
    }

    public function getResponseDelete200($resource)
    {
        return response()->json([
            'message' => "Your $resource has been successfully deleted!"
        ], 200);
    }

    /**
     * This feature allows you to customize the message for creating and updating a resource
     * $resource - affected object name (book, author, category, editorial, etc.)
     * Possible values for the variable $operation: created or updated.
     */
    public function getResponse201($resource, $operation, $data)
    {
        return response()->json([
            'message' => "Your $resource has been successfully $operation!",
            'data' => $data,
        ], 201);
    }

    public function getResponse400($resource)
    {
        return response()->json([
            'message' => "Something went wrong, $resource"
        ], 400);
    }

    public function getResponse401()
    {
        return response()->json([
            'message' => "Unauthorized"
        ], 401);
    }

    public function getResponse403()
    {
        return response()->json([
            'message' => "You don't have permission"
        ], 403);
    }

    public function getResponse404()
    {
        return response()->json([
            'message' => "The requested resource is not found"
        ], 404);
    }

    // public function getResponse500()
    // {
    //     return response()->json([
    //         'message' => "Something went wrong, please try again later"
    //     ], 500);
    // }

    //Función que recomienda el profe
    //Cambiar las funciones en los controladores
    public function getResponse500($errors)
    {
        return response()->json([
            'message' => "Something went wrong, please try again later",
            "errors" => $errors
        ], 500);
    }

    public function removeWhiteSpace($value) {
        return preg_replace('/\s+/', '\u0020', $value);
    }

}
