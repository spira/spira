<?php namespace App\Http\Validators;

use App\Services\Validator;

class TestValidator extends Validator
{
    /**
     * Validation rules.
     *
     * @return void
     */
    public function rules()
    {
        return [
            'varchar' => 'required|string',
            'hash' => 'required|string',
            'integer' => 'required|integer',
            'decimal' => 'required',
            'boolean' => 'required|boolean',
            'text' => 'required|string',
            'date' => 'required|date',
            'multi_word_column_title' => 'required|string'
        ];
    }
}
