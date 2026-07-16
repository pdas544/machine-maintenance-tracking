<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'machine_id' => $this->machine_id,
            'machine_code' => $this->machine?->machine_code,
            'line_or_group_name' => $this->machine?->linesOrGroup?->name,
            'issue_description' => $this->issue_description,
            'status' => $this->status,
            'raised_by_name' => $this->raiser?->name,
            'mechanic_name' => $this->mechanic?->name,
            'mechanic_remarks' => $this->mechanic_remarks,
            'acknowledged_at' => $this->acknowledged_at,
            'resolved_at' => $this->resolved_at,
            'raised_at' => $this->raised_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}