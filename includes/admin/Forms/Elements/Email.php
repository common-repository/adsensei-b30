<?php
namespace wpadsensei\Forms\Elements;

use wpadsensei\Forms\Elements;

/**
 * Class Email
 * @package wpadsensei\Forms\Elements
 */
class Email extends Elements
{

    /**
     * @return string
     */
    protected function prepareOutput()
    {
        return "<input id='{$this->getId()}' name='{$this->getName()}' type='email' {$this->prepareAttributes()} value='{$this->default}' />";
    }

    /**
     * @return string
     */
    public function render()
    {
        return ($this->renderFile) ? @file_get_contents($this->renderFile) : $this->prepareOutput();
    }
}