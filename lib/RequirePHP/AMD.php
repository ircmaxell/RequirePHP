<?php

namespace RequirePHP;

use React\Promise\Deferred;
use React\Promise\When;


class AMD {
    public $config;
    protected $aliases = array();
    protected $currentId = '';
    protected $exports;
    protected $modules = array();

    public function __construct(\StdClass $config = null) {
        if (!$config) {
            $config = new \StdClass;
        }
        $this->config = $config;
        if (!isset($this->config->paths)) {
            $this->config->paths = array();
        }
        $this->exports = new ExportStore;
        $self = $this;
        $this->exports->require = function($deps, $callback) use ($self) {
            return $self->with($deps, $callback);
        };
        $this->exports->with = $this->exports->require;
        $this->exports->define = function() use ($self) {
            return call_user_func_array(array($self, 'define'), func_get_args());
        };
        $this->exports->amd = $this;
        $this->exports->when = __NAMESPACE__ . '\when';

        $this->addModule(new Modules\DefaultModule);
        $this->addModule(new Modules\NewModule);
        $this->addModule(new Modules\TextModule);
    }

    public function getCurrentId() {
        return $this->currentId;
    }

    public function setCurrentId($id) {
        $this->currentId = $id;
    }

    public function alias($id, $id2) {
        $this->aliases[$id] = $id2;
        if ($this->exports->$id) {
            $this->exports->remove($id);
        }
        return $this;
    }

    public function protect($id, $value) {
        $this->addExport($id, $value);
    }

    public function aliasIfNotSet($id, $id2) {
        list ($tmpid, $module) = $this->parseId($id);
        if (null === $this->getExport($tmpid) && !isset($this->aliases[$tmpid])) {
            $this->alias($id, $id2);
        }
    }

    public function defineIfNotSet($factory) {
        list($id, $deps, $factory) = $this->parseArgs(func_get_args());
        list ($tmpid, $module) = $this->parseId($id);
        if (null === $this->getExport($tmpid) && !isset($this->aliases[$tmpid])) {
            $this->define($id, $deps, $factory);
        }
    }

    public function define($factory) {
        list($id, $deps, $factory) = $this->parseArgs(func_get_args());
        list ($id, $module) = $this->parseId($id);
        if (!$id) {
            $id = $this->currentId;
        }
        if ($module == 'raw' || (is_object($factory) && !$factory instanceof \Closure)) {
            $this->addExport($id, new Exports\Value($factory));
            return;
        } elseif (!is_callable($factory) && !(is_string($factory) && (class_exists($factory) || interface_exists($factory)))) {
            $this->addExport($id, new Exports\Value($factory));
            return;
        }
        $self = $this;

        $this->load($deps)->then(function($args) use ($id, $factory, $self) {
            if (is_callable($factory)) {
                $self->addExport($id, new Exports\FactorySingle($factory, $args));
            } else {
                $r = new \ReflectionClass($factory);
                $self->addExport($id, new Exports\Value($r->newInstanceArgs($args)));
            }
        });
    }

    public function load(array $deps) {
        static $stack = array();
        $deferreds = array();
        $self = $this;
        foreach ($deps as $dep) {
            list ($id, $module) = $this->parseId($dep);
            $export = $this->exports->$id;
            $skip = true;
            if ($export !== null) {
                $deferreds[] = $export;
            } else {
                if (array_search($dep, $stack)) {
                    throw new \RuntimeException("Circular Dependency Detected");
                }
                $stack[] = $dep;
                $skip = false;
            }
            if (!$skip && isset($this->aliases[$id])) {
                $alias = $this->aliases[$id];
                $localDef = $this->load(array($alias), $dep);
                $this->addExport($id, $localDef);
                list($aliasId) = $this->parseId($alias);
                $localDef->then(function() use ($id, $aliasId, $self) {
                    $self->aliasExport($aliasId, $id);
                });
                $deferreds[] = $localDef;
                unset($this->aliases[$dep]);
            } elseif (!$skip) {
                if (!isset($this->modules[$module])) {
                    throw new \RuntimeException('Unknown Module Provided: ' . $module);
                }
                $localDef = new Deferred;
                $this->addExport($id, $localDef);
                $this->modules[$module]->load($this, $id, function($result) use ($localDef, $id, $self) {
                    $self->addExport($id, $result);
                    if ($result instanceof Export) {
                        $result = $result->getValue();
                    }
                    When::resolve($result)->then(array($localDef, 'resolve'));
                });
                $deferreds[] = $localDef->promise();
            }
            if ($skip) {
                array_pop($stack);
            }
        }

        return When::all($deferreds)->then(function($args) {
            ksort($args);
            return $args;
        });
    }
    
    public function with($deps, $callback) {
        $cb = function($args) use ($callback) {
            return call_user_func_array($callback, $args);
        };
        if (is_array($deps)) {
            $this->load($deps)->then($cb);
        } elseif (is_string($deps)) {
            $this->load(array($deps))->then($cb);
        }
        return $this;
    }

    public function addExport($name, $object) {
        $this->exports->$name = $object;
    }

    public function aliasExport($from, $to) {
        $this->exports->alias($from, $to);
    }

    public function getExport($name) {
        return $this->exports->$name;
    }

    public function addModule(Module $module) {
        $this->modules[$module->getName()] = $module;
    }

    protected function parseId($id) {
        $module = '';
        if (strpos($id, '!') !== false) {
            list ($module, $id) = explode('!', $id, 2);
        }
        return array($id, $module);
    }

    protected function parseArgs(array $args) {
        $id   = $this->currentId;
        $deps = array();
        if (isset($args[2])) {
            if (is_string($args[0])) {
                $id = $args[0];
            } else {
                throw new \InvalidArgumentException("ID must be a string");
            }
            if (is_array($args[1])) {
                $deps = $args[1];
            } else {
                throw new \InvalidArgumentExceptioN("Arguments must be an array");
            }
            $factory = $args[2];
        } elseif (isset($args[1])) {
            if (is_string($args[0])) {
                $id = $args[0];
            } elseif (is_array($args[0])) {
                $deps = $args[0];
            } else {
                throw new \InvalidArgumentException('Could not parse first argument');
            }
            $factory = $args[1];
        } elseif (isset($args[0])) {
            $factory = $args[0];
        } else {
            throw new \InvalidArgumentException('A value must be provided');
        }
        return array($id, $deps, $factory);
    }

}