<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function setApiResponse($res, $status=0)
    {
        if (empty($status)) {
            $status = $this->setResponseStatus($res);
        }
        return response($res, $status);
    }

    public function setResponse($res, $status=0)
    {
        if (empty($status)) {
            $status = $this->setResponseStatus($res);
        }
        return response($res, $status);
    }

    public function setResponseStatus($res)
    {
        if ($res['metadata']['status'] == '0000') {
            return 200;
        } elseif ($res['metadata']['status'] == '100003') {
            return 403;
        } elseif ($res['metadata']['status'] == '99999') {
            return 404;
        } else {
            return 500;
        }
    }

    public function checkValidation($request, $rule)
    {
        $validator = Validator::make($request->all(), $rule);
        return $validator; 
    }
    public function failValidation($validator)
    {
        $res = [
            'metadata' => [
                'status' => '111111',
                'desc' => $validator->errors()->first()
            ],
            'data' => null
        ];
        return $this->setApiResponse($res, 400);
    }
}
