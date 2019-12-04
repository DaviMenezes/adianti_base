<?php
namespace Adianti\Base\Lib\Widget\Util;

use Adianti\Base\Lib\Widget\Base\TElement;

/**
 * TProgressBar
 *
 * @version    5.5
 * @package    widget
 * @subpackage util
 * @author     Ademilson Nunes
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TProgressBar extends TElement
{
    public $value;
    protected $mask;
    protected $className;
    
    public function __construct()
    {
        parent::__construct('div');
        $this->{'class'} = 'progress';
        $this->{'id'} = 'tprogressbar_'.mt_rand(1000000000, 1999999999);
        $this->{'style'} = 'margin-bottom:0; text-shadow: none;';
        $this->mask = '{value}%';
        $this->className = 'info';
    }

    /**
     * set mask for progress bar value Ex: "{value}%"
     * @param string $mask
     */
    public function setMask(string $mask)
    {
        $this->mask = $mask;
    }

    /**
     * set style class
     * @param string $class
     */
    public function setClass(string $class)
    {
        $this->className = $class;
    }

    /**
     * Set the value of progress bar
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        $progressBar = new TElement('div');
        $progressBar->{'class'} = "progress-bar progress-bar-{$this->className}";
        $progressBar->{'role'} = 'progressbar';
        $progressBar->{'arial-valuenow'} = $this->value;
        $progressBar->{'arial-valuemin'} = '0';
        $progressBar->{'arial-valuemax'} = '100';
        $progressBar->{'style'} = 'width: ' . $this->value . '%;';
         
        $value = str_replace('{value}', $this->value, $this->mask);
         
        $progressBar->add($value);
        parent::add($progressBar);
       
        parent::show();
    }
}
