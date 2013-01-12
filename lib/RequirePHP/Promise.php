<?php

namespace RequirePHP;

class Promise {

    protected $state = 'pending';

    protected $listeners = array(
        'done' => array(),
        'fail' => array(),
        'notify' => array(),
    );

    protected $resolved = false;
    protected $rejected = false;

    public function __construct(Promise $parent = null) {
        if ($parent) {
            $parent->done(array($this, 'resolve'));
            $parent->fail(array($this, 'reject'));
            $parent->progress(array($this, 'notify'));
        }
    }

    public function then($done = null, $fail = null, $progress = null) {
        if (is_callable($done)) {
            $this->done($done);
        }
        if (is_callable($fail)) {
            $this->fail($fail);
        }
        if (is_callable($progress)) {
            $this->progress($progress);
        }
        return $this;
    }

    public function always($callback) {
        $this->done($callback)->fail($callback);
    }

    public function done($callback) {
        $this->checkCallback($callback);
        if ($this->resolved) {
            call_user_func_array($callback, $this->args);
        } elseif (!$this->rejected) {
            $this->listeners['done'][] = $callback;
        }
        return $this;
    }

    public function fail($callback) {
        $this->checkCallback($callback);
        if ($this->rejected) {
            call_user_func_array($callback, $this->args);
        } elseif (!$this->rejected) {
            $this->listeners['fail'][] = $callback;
        }
        return $this;
    }

    public function progress($callback) {
        $this->checkCallback($callback);
        if (!$this->resolved && !$this->rejected) {
            $this->listeners['notify'][] = $callback;
        }
        return $this;
    }

    public function isRejected() {
        return $this->rejected;
    }

    public function isResolved() {
        return $this->resolved;
    }

    protected function notify() {
        $this->notifyWith(func_get_args());
        return $this;
    }

    protected function notifyWith(array $args) {
        if ($this->resolved || $this->rejected) {
            return;
        }
        foreach ($this->listeners['notify'] as $progress) {
            call_user_func_array($progress, $args);
        }
    }

    protected function reject() {
        $this->rejectWith(func_get_args());
    }

    protected function rejectWith(array $args) {
        if ($this->resolved || $this->rejected) {
            return $this;
        }
        $this->args = $args;
        $this->rejected = true;
        $this->state = 'rejected';
        foreach ($this->listeners['fail'] as $cb) {
            call_user_func_array($cb, $this->args);
        }
        $this->listeners = array();
        return $this;
    }

    protected function resolve() {
        $this->resolveWith(func_get_args());
    }

    protected function resolveWith(array $args) {
        if ($this->resolved || $this->rejected) {
            return $this;
        }
        $this->args = $args;
        $this->resolved = true;
        $this->state = 'resolved';
        foreach ($this->listeners['done'] as $cb) {
            call_user_func_array($cb, $this->args);
        }
        $this->listeners = array();
        return $this;
    }

    protected function _state() {
        return $this->state;
    }

    protected function checkCallback($callback) {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Invalid callback provided');
        }
    }

}