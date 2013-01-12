<?php

$amd->define(array('RequirePHPStubs\Interface1'), function($dep) {
    return get_class($dep);
});