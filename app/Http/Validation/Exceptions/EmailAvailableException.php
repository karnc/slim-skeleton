<?php

namespace App\Http\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class EmailAvailableException extends ValidationException
{

	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} 已经存在。',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} 还没有被使用。',
        ]
    ];
}