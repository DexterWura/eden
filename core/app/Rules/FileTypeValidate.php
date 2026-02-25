<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FileTypeValidate implements Rule
{
    protected $extensions;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($extensions)
    {

        $this->extensions = $extensions;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check extension first
        $extension = strtolower($value->getClientOriginalExtension());
        if (!in_array($extension, $this->extensions)) {
            return false;
        }

        // Check MIME type for additional security
        $mimeType = $value->getMimeType();
        $allowedMimeTypes = [
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        if (isset($allowedMimeTypes[$extension])) {
            return in_array($mimeType, $allowedMimeTypes[$extension]);
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute file type is not supported.';
    }
}
