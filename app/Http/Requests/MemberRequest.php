<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canHandleCashier() ?? false;
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('members', 'phone_number')->ignore($memberId)],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'points_balance' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
