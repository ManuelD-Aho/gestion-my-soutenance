<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdministrativeStaffRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['first_name' => 'required|string', 'last_name' => 'required|string']; }
}
