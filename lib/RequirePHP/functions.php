<?php

namespace RequirePHP;

function when() {
    $args = func_get_args();
    return whenAll($args);
}

function whenAll(array $args) {
    $toCall = count($args);
    $deferred = new Deferred;
    $results = array();
    foreach ($args as $key => $arg) {
        if (!$arg instanceof Promise) {
            throw new \InvalidArgumentException('When must be promises!');
        }
        $arg->done(function() use ($deferred, &$toCall, $key, &$results) {
            $toCall--;
            $args = func_get_args();
            $results[$key] = isset($args[0]) ? $args[0] : false;
            if ($toCall == 0) {
                $deferred->resolveWith($results);
            }
        })->fail(function() use ($deferred) {
            $deferred->reject();
        });
    }

    if (empty($args)) {
        $deferred->resolve();
    }
    return $deferred->promise();
}

