<?php

namespace RequirePHP\Exports;

class FactorySingle extends Factory {

    protected $value;

    public function getValue() {
        if (is_null($this->value)) {
            $this->value = parent::getValue();
        }
        return $this->value;
    }

}