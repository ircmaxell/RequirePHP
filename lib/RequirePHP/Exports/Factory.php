<?php

namespace RequirePHP\Exports;

class Factory implements \RequirePHP\Export {

    protected $factory;
    protected $arguments = array();

    public function __construct($factory, array $arguments = array()) {
        $this->factory = $factory;
        $this->arguments = $arguments;
    }

    public function getValue() {
        return call_user_func_array($this->factory, $this->arguments);
    }

}