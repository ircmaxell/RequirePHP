<?php

namespace RequirePHP;

class Deferred extends Promise {

    protected $promise;

    public function notify() {
        parent::notifyWith(func_get_args());
        return $this;
    }

    public function notifyWith(array $args) {
        parent::notifyWith($args);
        return $this;
    }

    public function promise() {
        if (!$this->promise) {
            $this->promise = new Promise($this);
        }
        return $this->promise;
    }

    public function reject() {
        parent::rejectWith(func_get_args());
        return $this;
    }

    public function rejectWith(array $args) {
        parent::rejectWith($args);
        return $this;
    }

    public function resolve() {
        parent::resolveWith(func_get_args());
        return $this;
    }

    public function resolveWith(array $args) {
        parent::resolveWith($args);
        return $this;
    }

    public function state() {
        return parent::_state();
    }

}