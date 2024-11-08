<?php
namespace wpadsensei;


/**
 * Class Elements
 * @package WPStaging\Forms
 */
abstract class Elements implements InterfaceElement
{

    /**
     * @var null|string
     */
    protected $name;

    /**
     * @var array
     */
    protected $attributes = array();

    /**
     * @var null|string
     */
    protected $label;

    /**
     * @var null|string|array
     */
    protected $default;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var array
     */
    protected $validations = array();

    /**
     * @var string
     */
    protected $renderFile;

    /**
     * Text constructor.
     * @param string $name
     * @param array $attributes
     */
    public function __construct($name, $attributes)
    {
        $this->setName($name);
        $this->setAttributes($attributes);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
       return $this->name;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        foreach ($attributes as $name => $value)
        {
            $this->setAttribute($name, $value);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function prepareAttributes()
    {
        $attributes = '';
        foreach ($this->attributes as $name => $value)
        {
            $attributes .= "{$name}='{$value}' ";
        }

        return rtrim($attributes, ' ');
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function prepareLabel()
    {
        return "<label for='{$this->getId()}'>{$this->label}</label>";
    }
    /**
     * @param string $label
     * @return $this
     */
    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTooltip()
    {
        return $this->tooltip;
    }

    /**
     * @return string
     */
    public function prepareTooltip()
    {
        return "<a class='adsensei-tooltip' href='#'></a><div class='adsensei-tooltip-message'>{$this->tooltip}</div>";
    }

    /**
     * @param array|string $filters
     * @return $this
     */
    public function setFilters($filters)
    {
        if (is_string($filters))
        {
            $this->filters[] = $filters;
        }
        else
        {
            array_merge($this->filters, $filters);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string|array $value
     * @return $this
     */
    public function setDefault($value)
    {
        $this->default = $value;

        return $this;
    }

    /**
     * @return null|string|array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param object $validation
     * @return $this
     */
    public function addValidation($validation)
    {
        $this->validations[] = $validation;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setRenderFile($file)
    {
        if (file_exists($file) && is_readable($file))
        {
            $this->renderFile = $file;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRenderFile()
    {
        return $this->renderFile;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param null|string $name
     * @return string
     */
    public function getId($name = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        if (!$name)
        {
            return '';
        }

        return str_replace(' ', '_', $name);
    }

    /**
     * @return string
     */
    abstract protected function prepareOutput();

    /**
     * @return string
     */
    abstract public function render();
}