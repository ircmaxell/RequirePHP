<?php

namespace RequirePHP\Modules;

use RequirePHP\Exports\FactorySingle;

class TwigModule implements \RequirePHP\Module {

    public function getName() {
        return 'twig';
    }

    public function load(\RequirePHP\AMD $amd, $name, $callback) {
        static $loaded = false;
        if (!$loaded) {
            $loaded = true;
            $amd->defineIfNotSet('raw!Twig_Loader_Filesystem.paths', $amd->config->paths);
            $amd->defineIfNotSet('raw!Twig_Loader_Array.templates', array());
            $amd->defineIfNotSet('raw!Twig_Environment.options', array());
            $amd->with(array('Twig_Loader_Filesystem', 'Twig_Loader_Array'), function($fs, $array) use ($amd) {
                $amd->aliasIfNotSet('Twig_LoaderInterface', 'Twig_Loader_Chain');
                $amd->defineIfNotSet('raw!Twig_Loader_Chain.loaders', array(
                    $fs, $array
                ));
            });
        }
        $amd->with(array('Twig_Environment'), function($twig) use ($name, $callback) {
            $callback(function($params) use ($twig, $name) {
                return $twig->render($name, $params);
            });
        });
    }

}
