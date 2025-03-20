<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait SendsResponse
{
    /**
     * @param $request
     * @return Response
     */
    public function toResponse($request): Response
    {

        $data = [
            'status'  =>  __($this->status),
            'message' => __($this->message),
            'body' => (object) $this->body,
            'errors' => $this->errors,
        ];
        return new JsonResponse(
            data: $data,
            status: $this->code->value,
        );
    }
}
