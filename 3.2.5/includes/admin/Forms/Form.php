<?php
namespace wpadsensei\Forms;

use wpadsensei\Forms\Elements\Interfaces\InterfaceElement;
use wpadsensei\Forms\Elements\Interfaces\InterfaceElementWithOptions;

/**
 * Class Form
 * @package wpadsensei\Forms
 */
class Form
{

    protected $elements = array();

    public function __construct()
    {

    }

    public function add($element)
    {
        if (!($element instanceof InterfaceElement) && !($element instanceof InterfaceElementWithOptions))
        {
            return;
        }

        $this->elements[$element->getName()] = $element;
    }

    public function render($name)
    {
        if (!isset($this->elements[$name]))
        {
            return false;
        }

        return $this->elements[$name]->render();
    }

    public function label($name)
    {
        if (!isset($this->elements[$name]))
        {
            return false;
        }

        return $this->elements[$name]->prepareLabel();
    }
}