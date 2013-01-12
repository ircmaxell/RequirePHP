<?php

namespace RequirePHP;

require_once __DIR__ . '/PromiseTest.php';

class DeferredTest extends PromiseTest {

    public static function provideTestLinks() {
        $return = array();
        $ret = array();
        $ret[] = new Deferred;
        $ret[] = $ret[0];
        $return[] = $ret;
        $ret = array();
        $ret[] = new Deferred;
        $ret[] = $ret[0]->promise();
        $return[] = $ret;
        return $return;
    }

    public function testCallbacksCalledDone() {
        $promise = $this->getPromise();
        $promise->resolveWith(array(1));
        $called = false;
        $promise->done(function($a) use (&$called) {
            if ($a == 1) {
                $called = true;
            }
        });
        $this->assertTrue($called);
        $called = false;
        $promise->fail(function() use (&$called) {
            $called = true;
        });
        $this->assertfalse($called);
        $called = false;
        $promise->progress(function() use (&$called) {
            $called = true;
        });
        $this->assertfalse($called);
    }

    public function testCallbacksCalledFail() {
        $promise = $this->getPromise();
        $promise->rejectWith(array(1));
        $called = false;
        $promise->done(function() use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
        $called = false;
        $promise->fail(function($a) use (&$called) {
            if ($a == 1) {
                $called = true;
            }
        });
        $this->assertTrue($called);
        $called = false;
        $promise->progress(function() use (&$called) {
            $called = true;
        });
        $this->assertfalse($called);
    }

    public function testGetState() {
        $promise = $this->getPromise();
        $this->assertEquals('pending', $promise->state());
        $promise->notify();
        $this->assertEquals('pending', $promise->state());
        $promise->notifyWith(array(1));
        $this->assertEquals('pending', $promise->state());
        $promise->reject();
        $this->assertEquals('rejected', $promise->state());
        $promise = $this->getPromise();
        $this->assertEquals('pending', $promise->state());
        $promise->resolve();
        $this->assertEquals('resolved', $promise->state());
    }

    public function testGetStateDoubleCall() {
        $promise = $this->getPromise();
        $this->assertEquals('pending', $promise->state());
        $promise->reject();
        $this->assertEquals('rejected', $promise->state());
        $promise->resolve();
        $this->assertEquals('rejected', $promise->state());
        $called = false;
        $promise->progress(function() use (&$called) {
            $called = true;
        });
        $this->assertfalse($called);
        $promise->notify();
    }

    public function testGetStateDoubleCall2() {
        $promise = $this->getPromise();
        $this->assertEquals('pending', $promise->state());
        $promise->resolve();
        $this->assertEquals('resolved', $promise->state());
        $promise->reject();
        $this->assertEquals('resolved', $promise->state());
        $called = false;
        $promise->progress(function() use (&$called) {
            $called = true;
        });
        $this->assertfalse($called);
        $promise->notify();
    }

    /**
     * @dataProvider provideNonCallableMethods
     */
    public function testNonCallableMethods($method) {
        $promise = $this->getPromise();
        $this->assertTrue(is_callable(array($promise, $method)));
    }

    public function testGetPromise() {
        $deferred = $this->getPromise();
        $promise = $deferred->promise();
        $this->assertTrue($promise instanceof Promise);
        $this->assertFalse($promise instanceof Deferred);
    }

    /**
     * @dataProvider provideTestLinks
     */
    public function testLinkDone($deferred, $promise) {
        $self = $this;
        $called = false;
        $alwaysCalled = false;
        $promise->done(function() use (&$called) {
            $called = true;
        });
        $promise->always(function() use (&$alwaysCalled) {
            $alwaysCalled = true;
        });
        $promise->fail(function() use ($self) {
            $self->fail('Fail called on deferred!');
        });
        $promise->progress(function() use ($self) {
            $self->fail('Progress called on deferred!');
        });
        $this->assertFalse($called);
        $this->assertFalse($alwaysCalled);
        $deferred->resolve();
        $this->assertTrue($called);
        $this->assertTrue($alwaysCalled);
    }

    /**
     * @dataProvider provideTestLinks
     */
    public function testLinkFail($deferred, $promise) {
        $self = $this;
        $called = false;
        $alwaysCalled = false;
        $promise->fail(function() use (&$called) {
            $called = true;
        });
        $promise->always(function() use (&$alwaysCalled) {
            $alwaysCalled = true;
        });
        $promise->done(function() use ($self) {
            $self->fail('Done called on deferred!');
        });
        $promise->progress(function() use ($self) {
            $self->fail('Progress called on deferred!');
        });
        $this->assertFalse($called);
        $this->assertFalse($alwaysCalled);
        $deferred->reject();
        $this->assertTrue($called);
        $this->assertTrue($alwaysCalled);
    }

    /**
     * @dataProvider provideTestLinks
     */
    public function testLinkNotify($deferred, $promise) {
        $self = $this;
        $called = false;
        $promise->always(function() use ($self) {
            $self->fail('Always called on deferred!');
        });
        $promise->fail(function() use ($self) {
            $self->fail('Fail called on deferred!');
        });
        $promise->done(function() use ($self) {
            $self->fail('Done called on deferred!');
        });
        $promise->progress(function() use (&$called) {
            $called = true;
        });
        $this->assertFalse($called);
        $deferred->notify();
        $this->assertTrue($called);
    }

    protected function getPromise($arg = null) {
        return new Deferred($arg);
    }

}