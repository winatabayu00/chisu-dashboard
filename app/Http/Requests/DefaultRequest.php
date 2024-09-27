<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\Service;
use App\Enums\Target;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DefaultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'region' => ['nullable', 'string'],
            'period' => ['nullable', 'array'],
            'period.type' => ['nullable', 'string', 'in:weekly,monthly,yearly'],
            'period.start' => ['nullable', 'date'],
            'period.end' => ['nullable', 'date'],
            'aggregate' => ['nullable', 'in:absolute,cumulative,percentage'],
            'gender' => ['nullable', Rule::in(Gender::options())],
            'target' => ['nullable', Rule::in(Target::options())],
            'indicator' => ['nullable', Rule::in(Service::allowMonthlyGrouping())],
        ];
    }
}
