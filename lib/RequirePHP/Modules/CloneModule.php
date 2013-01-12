<?php

namespace RequirePHP\Modules;

use RequirePHP\Exports\Factory;

class CloneModule extends NewModule {

    public function getName() {
        return 'clone';
    }

    protected function getLoadClassCallback($name, $callback) {
        return function() use ($name, $callback) {
            $instance = null;
            $factory = function($args) use ($name, &$instance) {
                if (!$instance) {
                    $r = new \ReflectionClass($name);
                    $instance = $r->newInstanceArgs($args);
                    return $instance;
                }
                return clone $instance;
            };
            $callback(new Factory($factory, func_get_args()));
        };
    }

}
