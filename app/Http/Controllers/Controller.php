<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function validationMessage($validationObj)
    {
        $response = [];
        foreach ($validationObj as $key => $value) {
            $obj = new \stdClass();
            $obj->name = $key;
            $obj->message = $value[0];

            array_push($response, $obj);
        }

        return $response;
    }
}
