<?php

return [

    'uuid'                 => 'The :attribute must be an UUID string.',
    'not_required_if'      => 'The :attribute must be null',
    'decimal'              => 'The :attribute must be a decimal.',
    'not_found'            => 'The selected :attribute is invalid.',
    'country'              => 'The :attribute must be a valid country code.',
    'string'               => 'The :attribute must be text',
    'decoded_json'         => 'The :attribute must be an object or an array',
    'alpha_dash_space'     => 'The :attribute may only contain letters, numbers, dashes and spaces.',
    'supported_region'     => 'The :attribute must be a supported region. Supported region codes are ('.implode(', ', array_pluck(config('regions.supported'), 'code')).')',

];
