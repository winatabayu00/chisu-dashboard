<?php

namespace Winata\PackageBased\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidationInput
{
    protected array $validatedData;

    /**
     * Validates inputs.
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @param array $attributes
     *
     * @return array|RedirectResponse
     *
     * @throws ValidationException
     */
    public function validate(array $inputs, array $rules, array $messages = [], array $attributes = []): array|RedirectResponse
    {
        $validator = Validator::make($inputs, $rules, $messages, $attributes);

//        if (request()->hasHeader('content-type') && request()->header('content-type') != 'application/json'){
//            if ($validator->fails()) {
//                return back()
//                    ->withErrors($validator)
//                    ->withInput();
//            }
//        }

        $this->setValidatedData($validator->validated());

        return $validator->validated();
    }


    /**
     * @param array $validatedData
     * @return ValidationInput
     */
    protected function setValidatedData(array $validatedData): self
    {
        $this->validatedData = $validatedData;
        return $this;
    }


    /**
     * @return array
     */
    protected function getValidatedData(): array
    {
        return $this->validatedData;
    }
}
