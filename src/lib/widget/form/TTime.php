<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Widget\Base\TScript;

/**
 * TimePicker Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TTime extends TEntry implements AdiantiWidgetInterface
{
    private $mask;
    protected $id;
    protected $size;
    protected $value;
    protected $options;
    protected $replaceOnPost;
    
    public function __construct($name)
    {
        parent::__construct($name);
        $this->id   = 'ttime_' . mt_rand(1000000000, 1999999999);
        $this->mask = 'hh:ii';
        $this->options = [];
        
        $this->setOption('startView', 1);
        $this->setOption('pickDate', false);
        $this->setOption('formatViewType', 'time');
        $this->setOption('fontAwesome', true);
    
        $newmask = $this->mask;
        $newmask = str_replace('hh', '99', $newmask);
        $newmask = str_replace('ii', '99', $newmask);
        parent::setMask($newmask);
        $this->tag->{'widget'} = 'ttime';
    }

    /**
     * Define the field's mask
     * @param string $mask Mask for the field (dd-mm-yyyy)
     * @param bool $replaceOnPost
     */
    public function setMask(string $mask, bool $replaceOnPost = false)
    {
        $this->mask = $mask;
        $this->replaceOnPost = $replaceOnPost;
        
        $newmask = $this->mask;
        $newmask = str_replace('hh', '99', $newmask);
        $newmask = str_replace('ii', '99', $newmask);
        
        parent::setMask($newmask, $replaceOnPost);
    }
    
    /**
     * Set extra datepicker options (ex: autoclose, startDate, daysOfWeekDisabled, datesDisabled)
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Enable the field
     * @param string $form_name Form name
     * @param string $field_name Field name
     */
    public static function enableField(string $form_name, string $field_name)
    {
        TScript::create(" tdate_enable_field('{$form_name}', '{$field_name}'); ");
    }

    /**
     * Disable the field
     * @param string $form_name Form name
     * @param object $field Field name
     */
    public static function disableField(string $form_name, object $field)
    {
        TScript::create(" tdate_disable_field('{$form_name}', '{$field}'); ");
    }

    /**
     * Shows the widget at the screen
     * @throws \Exception
     */
    public function show()
    {
        $language = strtolower(LANG);
        $options = json_encode($this->options);
        
        if (parent::getEditable()) {
            $outer_size = 'undefined';
            if (strstr($this->size, '%') !== false) {
                $outer_size = $this->size;
                $this->size = '100%';
            }
        }
        
        parent::show();
        
        if (parent::getEditable()) {
            TScript::create("tdatetime_start( '#{$this->id}', '{$this->mask}', '{$language}', '{$outer_size}', '{$options}');");
        }
    }
}
