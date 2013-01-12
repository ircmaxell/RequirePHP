<?php

namespace RequirePHP\Modules;

use RequirePHP\Exports\Factory;

class TextModule implements \RequirePHP\Module {

    public function getName() {
        return 'text';
    }

    public function load(\RequirePHP\AMD $amd, $name, $callback) {
        $paths = $amd->config->paths;
        foreach ($paths as $key => $value) {
            if (strpos($name, $key) !== 0) {
                continue;
            }
            $path = str_replace($key, $value, $name);
            if (!file_exists($path)) {
                continue;
            }
            $callback(file_get_contents($path));
        }
    }

}
