<?php

namespace App\Helpers;

use App\Enums\Http;
use App\Traits\SendsResponse;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class APIResponse extends Response implements Responsable
{
    use SendsResponse;

    /**
     * @param string $status
     * @param Http $code
     * @param string $message
     * @param array|object $body
     * @param array|null $errors
     */
    public function __construct(
        public readonly string $status = "success",
        public readonly Http $code = Http::OK,
        public readonly string $message = 'Request completed successfully',
        public readonly array|object $body =  [],
        public readonly ?array $errors = null,
    ) {
        parent::__construct();
    }
}
