<?php

namespace RequirePHP\Modules;

use RequirePHP\Deferred;
use RequirePHP\Promise;

class DefaultModule implements \RequirePHP\Module {

    public function getName() {
        return '';
    }

    public function load(\RequirePHP\AMD $amd, $name, $callback) {
        $module = $this->loadModule($amd, $name);
        if ($module) {
            $module->done($callback);
            return;
        }
        $class = $this->loadClass($amd, $name);
        if ($class) {
            $class->done($callback);
            return;
        }
        
        
        throw new \RuntimeException('Cannot locate module ' . $name);
    }

    protected function loadClass($amd, $name) {
        if (class_exists($name)) {
            $deps = $this->getDeps($name);
            if ($deps === false) {
                return false;
            }
            $deferred = new Deferred;
            $amd->load($deps)->done(function () use ($name, $deferred) {
                $r = new \ReflectionClass($name);
                $instance = $r->newInstanceArgs(func_get_args());
                $deferred->resolve($instance);
            });
            return $deferred->promise();
        }
        return false;
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
            $depClass = $param->getClass();
            if (!$depClass) {
                $deps[] = $class . '.' . $param->getName();
            } elseif ($depClass->getName() == $class) {
                throw new \RuntimeException('Cannot support classes with self-dependency');
            } else {
                $deps[] = $depClass->getName();
            }
        }
        return $deps;
    }

    protected function loadModule($amd, $name) {
            $paths = $amd->config->paths;
            foreach ($paths as $key => $value) {
                if (strpos($name, $key) !== 0) {
                    continue;
                }
                $path = str_replace($key, $value, $name);
                if (!file_exists($path . '/amd.php')) {
                    continue;
                }
            $current = $amd->getCurrentId();
            $amd->setCurrentId($name);
            $this->runLoader($amd, $path . '/amd.php');
            $amd->setCurrentId($current);
            if (!($export = $amd->getExport($name))) {
                continue;
            }
            if ($export instanceof Promise) {
                return $export;
            } else {
                $deferred = new Deferred;
                $deferred->resolve($export);
                return $deferred->promise();
            }
        }
        return false;
    }

    protected function runLoader($amd, $file) {
        include $file;
    }
}
