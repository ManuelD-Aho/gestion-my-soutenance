<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['first_name' => 'required|string', 'last_name' => 'required|string', 'student_card_number' => 'required|unique:students']; }
}
