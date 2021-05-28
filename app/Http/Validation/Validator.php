<?php

namespace App\Http\Validation;

use Respect\Validation\Validator as Respect;
use Respect\Validation\Exceptions\NestedValidationException;


class Validator
{
    protected $messages = [
        //Age
        '{{name}} must be between {{minAge}} and {{maxAge}} years ago' => '{{name}} 必须介于 {{minAge}} - {{maxAge}} 年之间。',
        '{{name}} must be lower than {{minAge}} years ago' => '{{name}} 必须小于 {{minAge}} 年。',
        '{{name}} must be greater than {{maxAge}} years ago' => '{{name}} 必须大于 {{maxAge}} 年。',

        '{{name}} must not be between {{minAge}} and {{maxAge}} years ago' => '{{name}} 必须不介于 {{minAge}} - {{maxAge}} 年之间。',
        '{{name}} must not be lower than {{minAge}} years ago' => '{{name}} 必须不小于 {{minAge}} 年。',
        '{{name}} must not be greater than {{maxAge}} years ago' => '{{name}} 必须不大于 {{maxAge}} 年。',

        //AllOf
        'All of the required rules must pass for {{name}}' => '必须为 {{name} 传递所有必需的规则',
        'These rules must pass for {{name}}' => '这些规则必须通过 {{name}}',

        'None of these rules must pass for {{name}}' => '所有这些规则都不能为 {{name}} 传递',
        'These rules must not pass for {{name}}' => '这些规则不能传递给 {{name}}',

        //Alnum
        '{{name}} must contain only letters (a-z) and digits (0-9)' => '{{name}} 只能由字母 (a-z) 和数字 (0-9) 组成。',
        '{{name}} must contain only letters (a-z), digits (0-9) and {{additionalChars}}' => '{{name}} 只能由字母 (a-z) 、数字 (0-9) 和 {{additionalChars}} 组成。',

        '{{name}} must not contain letters (a-z) or digits (0-9)' => '{{name}} 只能由字母 (a-z) 和数字 (0-9) 组成。',
        '{{name}} must not contain letters (a-z), digits (0-9) or {{additionalChars}}' => '{{name}} 只能由字母 (a-z) 、数字 (0-9) 和 {{additionalChars}} 组成。',

        //NoWhitespace
        '{{name}} must not contain whitespace' => '{{name}} 不能包含空格。',
        '{{name}} must not not contain whitespace' => '{{name}} 必须包含空格。',

        //NotEmpty
        'The value must not be empty' => '值不能为空。',
        '{{name}} must not be empty' => '{{name}} 不能为空。',

        'The value must be empty' => '值必须为空',
        '{{name}} must be empty' => '{{name}} 必须为空',

        //Email
        '{{name}} must be valid email' => '{{name}} 必须是有效的电子邮件。',
        '{{name}} must not be an email' => '{{name}} 不能是电子邮件。',

        
    ];

    protected $attributes = [
        'name' => '名称',
        'username' => '用户名',
        'email' => '邮箱',
        'first_name' => '名',
        'last_name' => '姓',
        'password' => '密码',
        'password_confirmation' => '确认密码',
        'city' => '城市',
        'country' => '国家',
        'address' => '地址',
        'phone' => '电话',
        'mobile' => '手机',
        'age' => '年龄',
        'sex' => '性别',
        'gender' => '性别',
        'day' => '天',
        'month' => '月',
        'year' => '年',
        'hour' => '时',
        'minute' => '分',
        'second' => '秒',
        'title' => '标题',
        'content' => '内容',
        'description' => '描述',
        'excerpt' => '摘要',
        'date' => '日期',
        'time' => '时间',
        'available' => '可用的',
        'size' => '大小',
    ];

    protected $errors;

    public function validate($request, array $rules)
    {

        //translate the errors
        $translator = function ($message) {
            $messages = $this->messages;
            if (isset($messages[$message])) {
                return $messages[$message];
            } else {
                return $message;
            }
        };

        foreach ($rules as $field => $rule) {
            try {
                if ($rule->getName()) {
                    $rule->assert($request->getParam($field));
                } else {
                    if (isset($this->attributes[$field])) {
                        $rule->setName($this->attributes[$field])->assert($request->getParam($field));
                    } else {
                        $rule->setName(ucfirst($field))->assert($request->getParam($field));
                    }
                }

            } catch (NestedValidationException $e) {
                $e->setParam('translator', $translator);
                $this->errors[$field] = $e->getMessages();
            }
        }

        $_SESSION['errors'] = $this->errors;
        return $this;
    }

    public function failed()
    {
        return !empty($this->errors);
    }
}

?>