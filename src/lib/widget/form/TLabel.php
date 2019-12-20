<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TStyle;

/**
 * Label Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TLabel extends TField implements AdiantiWidgetInterface
{
    private $embedStyle;
    protected $value;
    protected $size;
    protected $id;

    /**
     * Class Constructor
     * @param string|object $value text label
     * @param string|null $color
     * @param string|null $fontsize
     * @param string|null $decoration
     * @param string|null $size
     * @throws \ReflectionException
     */
    public function __construct(
        $value,
        string $color = null,
        string $fontsize = null,
        string $decoration = null,
        string $size = null
    ) {
        $this->id = mt_rand(1000000000, 1999999999);
        $stylename = 'tlabel_'.$this->id;
        
        // set the label's content
        $this->setValue($value);
        
        $this->embedStyle = new TStyle($stylename);
        
        if (!empty($color)) {
            $this->setFontColor($color);
        }
        
        if (!empty($fontsize)) {
            $this->setFontSize($fontsize);
        }
        
        if (!empty($decoration)) {
            $this->setFontStyle($decoration);
        }
        
        if (!empty($size)) {
            $this->setSize($size);
        }
        
        // create a new element
        $this->tag = new TElement('label');
    }
    
    /**
     * Clone the object
     */
    public function __clone()
    {
        parent::__clone();
        $this->embedStyle = clone $this->embedStyle;
    }

    /**
     * Define the font size
     * @param string $size Font size in pixels
     */
    public function setFontSize(string $size)
    {
        $this->embedStyle->{'font_size'}    = (strpos($size, 'px') or strpos($size, 'pt')) ? $size : $size.'pt';
    }

    /**
     * Define the style
     * @param string $decoration text decorations (b=bold, i=italic, u=underline)
     */
    public function setFontStyle(string $decoration)
    {
        if (strpos(strtolower($decoration), 'b') !== false) {
            $this->embedStyle->{'font-weight'} = 'bold';
        }
        
        if (strpos(strtolower($decoration), 'i') !== false) {
            $this->embedStyle->{'font-style'} = 'italic';
        }
        
        if (strpos(strtolower($decoration), 'u') !== false) {
            $this->embedStyle->{'text-decoration'} = 'underline';
        }
    }

    /**
     * Define the font face
     * @param string $font Font Family Name
     */
    public function setFontFace(string $font)
    {
        $this->embedStyle->{'font_family'} = $font;
    }

    /**
     * Define the font color
     * @param string $color Font Color
     */
    public function setFontColor(string $color)
    {
        $this->embedStyle->{'color'} = $color;
    }
    
    /**
     * Add a content inside the label
     * @param $content
     */
    public function add($content)
    {
        $this->tag->add($content);
        
        if (is_string($content)) {
            $this->value .= $content;
        }
    }
    
    /**
     * Get value
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Shows the widget at the screen
     */
    public function show()
    {
        if ($this->size) {
            if (strstr($this->size, '%') !== false) {
                $this->embedStyle->{'width'} = $this->size;
            } else {
                $this->embedStyle->{'width'} = $this->size . 'px';
            }
        }
        
        // if the embed style has any content
        if ($this->embedStyle->hasContent()) {
            $this->setProperty('style', $this->embedStyle->getInline() . $this->getProperty('style'), true);
        }
        
        // add content to the tag
        $this->tag->add($this->value);
        
        // show the tag
        $this->tag->show();
    }
}
