<?php

namespace App\Http\Resources;

class ApiResponse
{
    private $data;
    private $status;
    private $description;
    private $pagination;
    private $http_code;

    /**
     * set data
     *
     * @param array|null $data
     */
    public function __construct(?array $data)
    {
        $this->data = $data;
        $mata_data = config('response.base.success');
        $this->status = $mata_data['status'];
        $this->description = $mata_data['desc'];
        $this->http_code = 200;
    }

    /**
     * set metadata status
     *
     * @param string $status
     * @return void
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * set metadata description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * set metadata pagination
     *
     * @param array $pagination
     * @return void
     */
    public function setPagination(array $pagination)
    {
        $this->pagination = $pagination;
        return $this;
    }

    /**
     * set http status code
     *
     * @param integer $http_code
     * @return void
     */
    public function setHttpCode(int $http_code)
    {
        $this->http_code = $http_code;
        return $this;
    }

    /**
     * json response
     *
     * @return void
     */
    public function toJsonResponse()
    {
        return response()->json($this->format(), $this->http_code);
    }

    /**
     * response format
     *
     * @return void
     */
    protected function format()
    {
        $response = [
            'metadata' => [
                'status' => $this->status,
                'desc' => $this->description,
            ],
            'data' => $this->data,
        ];

        if ($this->pagination !== null) {
            $response['metadata']['pagination'] = $this->pagination;
        }

        return $response;
    }
}