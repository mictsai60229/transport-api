<?php

namespace App\Exceptions;

use App\Http\Resources\ApiResponse;
use Exception;
use Illuminate\Http\Request;

abstract class AbstractCustomException extends Exception
{
    /** data */
    protected $data = null;

    /** metadata */
    protected $metadata;

    /** http code */
    protected $http_code = 500;

    /** exception */
    protected $exception;

    /**
     * log exception if not null
     *
     * @return void
     */
    public function report()
    {
    }

    /**
     * render
     *
     * @param Request $request
     * @return void
     */
    public function render(Request $request)
    {
        if (empty($this->metadata)) {
            $this->metadata = config('response.base.system_error');
        }

        return (new ApiResponse($this->data))
                ->setStatus($this->metadata['status'])
                ->setDescription($this->metadata['desc'])
                ->setHttpCode($this->http_code)
                ->toJsonResponse();
    }
}