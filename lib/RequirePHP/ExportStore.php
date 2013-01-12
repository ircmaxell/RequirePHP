<?php

namespace RequirePHP;

class ExportStore {
    protected $exports = array();
    protected $thisExport;

    public function __construct() {
        $this->thisExport = new Exports\Value($this);
    }

    public function __get($id) {
        $export = $this->getExport($id);
        if ($export) {
            return $export->getValue();
        }
        return null;
    }

    public function __set($id, $value) {
        if (!$value instanceof Export) {
            $value = new Exports\Value($value);
        }
        $this->exports[$id] = $value;
    }

    public function alias($from, $to) {
        $fromExp = $this->getExport($from);
        if ($fromExp) {
            $this->exports[$to] = $fromExp;
        } else {
            throw new \RuntimeException('Attempting to alias non-existant export: ' . $from);
        }
    }

    public function getExport($id) {
        if ($id == 'exports') {
            return $this->thisExport;
        }
        if (isset($this->exports[$id])) {
            return $this->exports[$id];
        }
        return false;
    }

    public function remove($id) {
        unset($this->exports[$id]);
    }
}
