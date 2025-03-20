<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge([
            'id' => $this->id,
            'ticket_id' => $this->ticket_id,
            'ticket_name' => $this->ticket_name,
            'status' => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            'user' => new UserResource($this->user)
        ]);
    }
}
