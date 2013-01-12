<?php

require_once 'vendor/autoload.php';

$amd = new RequirePHP\AMD;
$amd->config->paths['Test'] = __DIR__ . '/test/stubs/RequirePHPStubs';
$amd->addModule(new RequirePHP\Modules\TwigModule);

//**
$amd->define('bar', function() { return 'bar'; });

$amd->alias('RequirePHPStubs\Interface1', 'new!RequirePHPStubs\Class1');

$amd->alias('bar', 'RequirePHPStubs\Interface1');

$amd->define('baz', function() {
    return new StdClass;
});

$amd->with(array('bar', 'baz'), function($bar, $baz) {
    global $a, $b;
    $a = $bar;
    $b = $baz;
});

$amd->with(array('RequirePHPStubs\Class1', 'baz'), function($bar, $baz) {
    global $a, $b, $c;
    if ($a === $bar) {
        die('FAIL');
    }
    if ($b !== $baz) {
        die('FAIL2');
    }
});

$amd->with(array('Test/Module1'), function($module) {
    var_dump($module);
});
/*/

$amd->with(array('twig!test.twig'), function($template) {
    echo $template(array('foo' => 'bar'));
});

//*/

echo "hi!";