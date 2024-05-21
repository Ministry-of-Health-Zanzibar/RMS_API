<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'full_name' => $this->first_name." ". $this->middle_name." ". $this->last_name,
            'token' =>$this->createToken('auth_token')->plainTextToken,
            'login_status'=>$this->login_status,
            'statusCode' => 200,
            // 'permission' => $this->permissions
        ];
    }
}
