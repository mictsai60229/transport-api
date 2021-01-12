<?php

namespace App\Exceptions;

use App\Exceptions\AbstractCustomException;

class ValidationException extends AbstractCustomException
{
    protected $metadata;
    protected $http_code;

    public function __construct(string $invalid_desc)
    {
        $this->metadata = config('response.base.invalid_input');
        $this->metadata['desc'] = $invalid_desc;
        $this->http_code = 400;
    }
}
