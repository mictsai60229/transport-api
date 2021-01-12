<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Formatters\Response\ResponseFormatter;
use App\Http\Resources\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class ApiController extends BaseController
{
    /**
     * 驗證request的參數
     *
     * @param Request $request
     * @param array $validation_rule
     * @return void
     */
    protected function validateRequest(Request $request, array $validation_rule)
    {
        $validator = Validator::make($request->all(), $validation_rule);
        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->first());
        }
    }

    /**
     * render response
     *
     * @param ResponseFormatter $formatter
     * @return void
     */
    protected function renderApiResonse(ResponseFormatter $formatter)
    {
        $response = new ApiResponse($formatter->getData());
        if ($formatter instanceof SearchFormatter) {
            $response->setPagination($formatter->getPagination());
        }
        return $response->toJsonResponse();
    }
}