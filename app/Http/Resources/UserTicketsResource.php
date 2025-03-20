<?php

namespace App\Http\Resources;

use App\Http\Resources\TicketCollection;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTicketsResource extends JsonResource
{
    private $additionalData;

    public function __construct($resource, $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge([
            new UserResource($this,
            ["tickets" =>new TicketCollection($this->tickets)])
        ],$this->additionalData);
    }
}
