<?php
namespace Adianti\Base\Lib\Widget\Container;

use Adianti\Base\Lib\Widget\Base\TElement;

/**
 * Scrolled Window: Allows to add another containers inside, creating scrollbars when its content is bigger than its visual area
 *
 * @version    5.5
 * @package    widget
 * @subpackage container
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TScroll extends TElement
{
    private $width;
    private $height;
    private $margin;
    private $transparency;
    
    public function __construct()
    {
        $this->{'id'} = 'tscroll_' . mt_rand(1000000000, 1999999999);
        $this->margin = 2;
        $this->transparency = false;
        parent::__construct('div');
    }
    
    /**
     * Set the scroll size
     * @param $width Panel's width
     * @param $height Panel's height
     */
    public function setSize(string $width, string $height)
    {
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Set the scrolling margin
     * @param string $margin Margin
     */
    public function setMargin(string $margin)
    {
        $this->margin = $margin;
    }

    /**
     * compability reasons
     * @param $bool
     */
    public function setTransparency(bool $bool)
    {
        $this->transparency = $bool;
    }
    
    /**
     * Shows the tag
     */
    public function show()
    {
        if (!$this->transparency) {
            $this->{'style'} .= ';border: 1px solid #c2c2c2';
            $this->{'style'} .= ';background: #ffffff';
        }
        $this->{'style'} .= ";padding: {$this->margin}px";
        
        if ($this->width) {
            $this->{'style'} .= (strstr($this->width, '%') !== false) ? ";width:{$this->width}" : ";width:{$this->width}px";
        }
        
        if ($this->height) {
            $this->{'style'} .= (strstr($this->height, '%') !== false) ? ";height:{$this->height}" : ";height:{$this->height}px";
        }
        
        $this->{'class'} .= " tscroll";
        parent::show();
    }
}
