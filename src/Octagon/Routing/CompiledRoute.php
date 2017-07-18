<?php

namespace Octagon\Routing;

class CompiledRoute
{
    // the compiled regex
    private $_regex;

    private $_pathVariables;

    private $_hostVariables;

    public function __construct($regex, $pathVariables = array(), $hostVariables = array())
    {
        $this->_setRegex($regex);
        $this->_setPathVariables($pathVariables);
        $this->_setHostVariables($hostVariables);
    }

    private function _setRegex($regex)
    {
        $this->_regex = $regex;
    }

    public function getRegex()
    {
        return $this->_regex;
    }

    private function _setPathVariables($pathVariables)
    {
        $this->_pathVariables = $pathVariables;
    }

    public function getPathVariables()
    {
        return $this->_pathVariables;
    }

    private function _setHostVariables($hostVariables)
    {
        $this->_hostVariables = $hostVariables;
    }

    public function getHostVariables()
    {
        return $this->_hostVariables;
    }
}
