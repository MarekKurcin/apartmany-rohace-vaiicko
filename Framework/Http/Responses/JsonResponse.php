<?php

namespace Framework\Http\Responses;

/**
 * Class JsonResponse
 *
 * Represents an HTTP response that returns data in JSON format. This class is specifically designed to simplify
 * the process of creating and sending JSON responses to clients in an API.
 *
 * @package App\Core\Responses
 */
class JsonResponse extends Response
{
    /**
     * The data to be encoded as JSON and sent in the response.
     *
     * @var mixed
     */
    private mixed $data;

    /**
     * JsonResponse constructor.
     *
     * Initializes a new instance of JsonResponse with the specified data.
     *
     * @param mixed $data The data to be returned in the JSON response. This can be an array, an object, or any other
     * type that can be converted to JSON.
     */
    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    /**
     * Generates the JSON response.
     *
     * This method sets the Content-Type header to 'application/json' and outputs the JSON-encoded data to the client.
     * It is called when the response is sent, ensuring that the data is properly formatted for JSON communication.
     *
     * @return void
     */
    protected function generate(): void
    {
        header('Content-Type: application/json');
        print(json_encode($this->data));
        flush();
    }
}
