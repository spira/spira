<?php

namespace App\Http\Validators;

use App\Services\Validator;

class TestEntityValidator extends Validator
{
    /**
     * Validation rules.
     *
     * @var array
     */
    protected $rules = [
        'varchar'                 => 'required|string',
        'hash'                    => 'required|string',
        'integer'                 => 'required|integer',
        'decimal'                 => 'required|float',
        'boolean'                 => 'required|boolean',
        'text'                    => 'required|string',
        'date'                    => 'required|date',
        'multi_word_column_title' => 'required|boolean',
        'hidden'                  => 'required|boolean',
    ];

    /**
     * Model being validated.
     *
     * @var string
     */
    protected function model()
    {
        return 'App\Models\TestEntity';
    }
}
