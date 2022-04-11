<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkNoticeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return WorkNoticeResource::collection($this->collection);
    }
}
