<?php

namespace App\Services;

class Token
{
    public $decoded;

    public function hydrate($decoded)
    {
        $this->decoded = $decoded;
    }

    public function getScope()
    {
        return (array)$this->decoded->scope;
    }
}

?>