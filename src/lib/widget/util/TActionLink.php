<?php
namespace Adianti\Base\Lib\Widget\Util;

use Adianti\Base\Lib\Control\TAction;

/**
 * Action Link
 *
 * @version    5.5
 * @package    widget
 * @subpackage util
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TActionLink extends TTextDisplay
{
    /**
     * @var TAction
     */
    protected $action;

    /**
     * Class Constructor
     * @param  $value  text content
     * @param  $action TAction Object
     * @param  $color  text color
     * @param  $size   text size
     * @param  $decoration text decorations (b=bold, i=italic, u=underline)
     */
    public function __construct($value, TAction $action, $color = null, $size = null, $decoration = null, $icon = null)
    {
        if ($icon)
        {
            $value = new TImage($icon) . $value;
        }
        
        parent::__construct($value, $color, $size, $decoration);
        parent::setName('a');
        $this->action = $action;
        $this->{'generator'} = 'adianti';
    }
    
    /**
     * Add CSS class
     */
    public function addStyleClass($class)
    {
        $this->{'class'} .= " {$class}";
    }

    public function getAction():TAction
    {
        return $this->action;
    }

    public function show()
    {
        $this->{'href'} = $this->action->serialize();
        parent::show();
    }
}
