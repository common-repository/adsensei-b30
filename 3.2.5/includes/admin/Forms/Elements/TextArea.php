<?php
namespace wpadsensei\Forms\Elements;

use wpadsensei\Forms\Elements;

/**
 * Class TextArea
 * @package wpadsensei\Forms\Elements
 */
class TextArea extends Elements
{

    /**
     * @return string
     */
    protected function prepareOutput()
    {
        return "<textarea id='{$this->getId()}' name='{$this->getName()}' {$this->prepareAttributes()}>{$this->default}</textarea>";
    }

    /**
     * @return string
     */
    public function render()
    {
        return ($this->renderFile) ? @file_get_contents($this->renderFile) : $this->prepareOutput();
    }
}