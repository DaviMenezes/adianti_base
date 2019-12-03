<?php
namespace Adianti\Base\Lib\Widget\Container;

use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Base\TElement;

/**
 * Expander Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TExpander extends TElement
{
    private $container;
    private $button;
    private $caret_side;
    private $label;

    /**
     * Class Constructor
     * @param string $label
     */
    public function __construct(string $label = '')
    {
        parent::__construct('div');
        $this->{'id'}    = 'texpander_'.mt_rand(1000000000, 1999999999);
        $this->{'class'} = 'dropdown';
        
        $this->button = new TElement('button');
        $this->button->{'class'} = 'btn btn-default dropdown-toggle';
        $this->button->{'type'} = 'button';
        $this->button->{'id'}   = 'button_'.mt_rand(1000000000, 1999999999);
        $this->button->{'data-toggle'} = 'dropdown';
        $this->label = $label;
        
        $this->container = new TElement('ul');
        $this->container->{'class'} = 'dropdown-menu texpander-container';
        
        $this->container->{'aria-labelledby'} = $this->button->{'id'};
        
        parent::add($this->button);
        parent::add($this->container);
    }
    
    /**
     * Set caret side
     * @param string $caret_side Caret side (left, right)
     */
    public function setCaretSide($caret_side)
    {
        $this->caret_side = $caret_side;
    }
    
    /**
     * Define the pull side
     * @param string side left/right
     */
    public function setPullSide(string $side)
    {
        $this->container->{'class'} = "dropdown-menu texpander-container pull-{$side}";
    }

    /**
     * Define a button property
     * @param string $property Property name (Ex: style)
     * @param string $value Property value
     */
    public function setButtonProperty(string $property, $value)
    {
        $this->button->$property = $value;
    }

    /**
     * Define a container property
     * @param string $property Property name (Ex: style)
     * @param string $value Property value
     */
    public function setProperty(string $property, $value)
    {
        $this->container->$property = $value;
    }
    
    /**
     * Add content to the expander
     * @param mixed $content Any Object that implements show() method
     */
    public function add($content)
    {
        $this->container->add($content);
    }
    
    /**
     * Shows the expander
     */
    public function show()
    {
        if ($this->caret_side == 'left') {
            $this->button->add(TElement::tag('span', '', array('class'=>'caret')));
            $this->button->add($this->label);
        } elseif ($this->caret_side == 'right') {
            $this->button->add($this->label);
            $this->button->add('&nbsp');
            $this->button->add(TElement::tag('span', '', array('class'=>'caret')));
        } else {
            $this->button->add($this->label);
        }
        
        parent::show();
        TScript::create('texpander_start();');
    }
}
