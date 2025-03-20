<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TicketCollection extends ResourceCollection
{

    private $additionalData;

    public function __construct($resource, $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {

        return array_merge([
            'data' => $this->collection,
            'links' => [
                'self' => url('/api/tickets'),
            ],
        ], $this->additionalData);
    }
}
