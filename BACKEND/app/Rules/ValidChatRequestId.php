<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ChatbotMessageRequest;

class ValidChatRequestId implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $decryptedId = decryptString($value);

            if (!ChatbotMessageRequest::where('id', $decryptedId)->exists()) {
                $fail("The chat request id is invalid.");
            }
        } catch (\Exception $e) {
            $fail("The chat request id is invalid.");
        }
    }
}
