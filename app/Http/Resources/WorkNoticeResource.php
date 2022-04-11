<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkNoticeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return [
            'notice_id' => $this->id,
            'name' => $this->name,
            'receive' => $this->user->name,
            'content' => $this->content,
            'is_read' => $this->is_read,
            'created_at' =>$this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
