<?php

namespace RequirePHP;

class PromiseTest extends \PHPUnit_Framework_TestCase {

    public static function staticCallback() {
    }

    public function nonStaticCallback() {
    }

    protected function protectedNonStaticCallback() {
    }

    public static function provideCallbackMethods() {
        return array(
            array('done'),
            array('fail'),
            array('always'),
        );
    }

    public static function provideNonCallableMethods() {
        return array(
            array('notify'),
            array('notifyWith'),
            array('resolve'),
            array('resolveWith'),
            array('reject'),
            array('rejectWith'),
            array('state')
        );
    }

    public function testConstruct() {
        $promise = $this->getPromise();
        $this->assertTrue($promise instanceof Promise);
    }

    /**
     * @dataProvider provideNonCallableMethods
     */
    public function testNonCallableMethods($method) {
        $promise = $this->getPromise();
        $this->assertFalse(is_callable(array($promise, $method)));
    }

    /**
     * @dataProvider provideCallbackMethods
     */
    public function testCallbacks($method) {
        $promise = $this->getPromise();
        $this->assertTrue(is_callable(array($promise, $method)));
        $promise->$method('is_null');
        $promise->$method(function() {});
        $promise->$method(array(__CLASS__, 'staticCallback'));
        $promise->$method(array($this, 'nonStaticCallback'));
    }

    /**
     * @dataProvider provideCallbackMethods
     * @expectedException InvalidArgumentException
     */
    public function testCallbackFailure1($method) {
        $promise = $this->getPromise();
        $promise->$method('-asfgsdzgdzggz');
    }

    /**
     * @dataProvider provideCallbackMethods
     * @expectedException InvalidArgumentException
     */
    public function testCallbackFailure2($method) {
        $promise = $this->getPromise();
        $promise->$method(array($this, 'protectedNonStaticCallback'));
    }

    public function testIsResolvedDefault() {
        $promise = $this->getPromise();
        $this->assertFalse($promise->isResolved());
    }

    public function testIsRejectedDefault() {
        $promise = $this->getPromise();
        $this->assertFalse($promise->isRejected());
    }

    public function testConstructorParam() {
        $mock = $this->getPromiseMock();
        $mock->expects($this->once())
             ->method('done');
        $mock->expects($this->once())
             ->method('fail');
        $mock->expects($this->once())
             ->method('progress');
        $this->getPromise($mock);
    }

    protected function getPromise($arg = null) {
        return new Promise($arg);
    }

    protected function getPromiseMock(array $args = array()) {
        $mock = $this->getMock(__NAMESPACE__ . '\Promise', array(), $args);
        return $mock;
    }

}