<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Widget\Base\TElement;

/**
 * Form separator
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TFormSeparator extends TElement
{
    private $fontColor;
    private $separatorColor;
    private $fontSize;
    private $header;
    private $divisor;

    /**
     * Class Constructor
     * @param string $text Separator title
     * @param string $fontColor
     * @param string $fontSize
     * @param string $separatorColor
     */
    public function __construct(
        string $text,
        string $fontColor = '#333333',
        string $fontSize = '16',
        string $separatorColor = '#eeeeee'
    ) {
        parent::__construct('div');
        
        $this->fontColor = $fontColor;
        $this->separatorColor = $separatorColor;
        $this->fontSize = $fontSize;
        
        $this->header = new TElement('h4');
        $this->header->{'class'} = 'tseparator';
        $this->header->{'style'} = "font-size: {$this->fontSize}px; color: {$this->fontColor};";
        
        $this->divisor = new TElement('hr');
        $this->divisor->{'style'} = "border-top-color: {$this->separatorColor}";
        $this->divisor->{'class'} = 'tseparator-divisor';
        $this->header->add($text);

        $this->add($this->header);
        $this->add($this->divisor);
    }

    /**
     * Set font size
     * @param string $size font size
     */
    public function setFontSize(string $size)
    {
        $this->fontSize = $size;
        $this->header->{'style'} = "font-size: {$this->fontSize}px; color: {$this->fontColor};";
    }

    /**
     * Set font color
     * @param string $color font color
     */
    public function setFontColor(string $color)
    {
        $this->fontColor = $color;
        $this->header->{'style'} = "font-size: {$this->fontSize}px; color: {$this->fontColor};";
    }

    /**
     * Set separator color
     * @param string $color separator color
     */
    public function setSeparatorColor(string $color)
    {
        $this->separatorColor = $color;
        $this->divisor->{'style'} = "border-top-color: {$this->separatorColor}";
    }
}
