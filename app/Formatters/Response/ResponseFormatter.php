<?php

namespace App\Formatters\Response;

abstract class ResponseFormatter
{
    /**
     * get data for response
     *
     * @return void
     */
    abstract public function getData();
}