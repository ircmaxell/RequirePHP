<?php

namespace RequirePHP;

interface Module {

    public function getName();
    
    public function load(\RequirePHP\AMD $amd, $name, $callback);

}