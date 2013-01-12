<?php

namespace RequirePHP;

class functionsTest extends \PHPUnit_Framework_TestCase {

    public function testWhenReturnsPromise() {
        $promise = when();
        $this->assertTrue($promise instanceof Promise);
        $this->assertTrue($promise->isResolved());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhenInvalidArg() {
        when('test');
    }

    public function testWhenFunctionality() {
        $deferred = new Deferred;
        $promise = when($deferred->promise());
        $this->assertTrue($promise instanceof Promise);
        $this->assertFalse($promise->isResolved());
        $this->assertFalse($promise->isRejected());
        $deferred->resolve();
        $this->assertTrue($promise->isResolved());
        $this->assertFalse($promise->isRejected());
    }

    public function testWhenFunctionalityFail() {
        $deferred = new Deferred;
        $promise = when($deferred->promise());
        $this->assertTrue($promise instanceof Promise);
        $this->assertFalse($promise->isResolved());
        $this->assertFalse($promise->isRejected());
        $deferred->reject();
        $this->assertFalse($promise->isResolved());
        $this->assertTrue($promise->isRejected());
    }

}