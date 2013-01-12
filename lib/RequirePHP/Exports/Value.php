<?php

namespace RequirePHP\Exports;

class Value implements \RequirePHP\Export {

    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }

}