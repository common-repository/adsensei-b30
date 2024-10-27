<?php
namespace wpadsensei\Forms\Elements;

use wpadsensei\Forms\Elements;

/**
 * Class Text
 * @package wpadsensei\Forms\Elements
 */
class Text extends Elements
{

    /**
     * @return string
     */
    protected function prepareOutput()
    {
        return "<input id='{$this->getId()}' name='{$this->getName()}' type='text' {$this->prepareAttributes()} value='{$this->default}' />";
    }

    /**
     * @return string
     */
    public function render()
    {
        return ($this->renderFile) ? @file_get_contents($this->renderFile) : $this->prepareOutput();
    }
}