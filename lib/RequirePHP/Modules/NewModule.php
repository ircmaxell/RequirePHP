<?php

namespace RequirePHP\Modules;

use RequirePHP\Exports\Factory;

class NewModule implements \RequirePHP\Module {

    public function getName() {
        return 'new';
    }

    public function load(\RequirePHP\AMD $amd, $name, $callback) {
        if (!class_exists($name)) {
            throw new \InvalidArgumentException('Class not found!');
        }
        $deps = $this->getDeps($name);

        $amd->load($deps)->done($this->getLoadClassCallback($name, $callback));
    }

    protected function getLoadClassCallback($name, $callback) {
        return (function() use ($name, $callback) {
            $args = func_get_args();
            $factory = new Factory(
                    function() use ($name) {
                        $r = new \ReflectionClass($name);
                        return $r->newInstanceArgs(func_get_args());
                    },
                    $args
            );
            $callback($factory);
        });
    }

    protected function getDeps($class) {
        $r = new \ReflectionClass($class);
        $deps = array();
        $ctor = $r->getConstructor();
        if (!$ctor) {
            return array();
        } elseif (!$ctor->isPublic()) {
            return false;
        }
        foreach ($ctor->getParameters() as $param) {
            $class = $param->getClass();
            if (!$class) {
                return false;
            }
            $deps[] = $class->getName();
        }
        return $deps;
    }

}
