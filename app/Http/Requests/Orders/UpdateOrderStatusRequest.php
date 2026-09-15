<?php

namespace App\Http\Requests\Orders;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatus::class)],
            'reason' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->input('status') === OrderStatus::Cancelled->value)],
            'cancelled_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function getStatusEnum(): OrderStatus
    {
        return OrderStatus::from($this->validated('status'));
    }
}
