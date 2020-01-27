<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Widget\Base\TScript;
use DateTime;
use Dvi\Component\Widget\Form\Field\Varchar;

/**
 * DatePicker Widget
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TDate extends Varchar implements AdiantiWidgetInterface
{
    protected $mask;
    private $dbmask;
    protected $options;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->setProperty('id', 'tdate_' . mt_rand(1000000000, 1999999999));
        $this->mask = 'yyyy-mm-dd';
        $this->dbmask = null;
        $this->options = [];
        $this->replaceOnPost = false;
        
        $newmask = $this->mask;
        $newmask = str_replace('dd', '99', $newmask);
        $newmask = str_replace('mm', '99', $newmask);
        $newmask = str_replace('yyyy', '9999', $newmask);
        parent::setMask($newmask);
        $this->tag->{'widget'} = 'tdate';
    }
    
    public function setValue(?string $value)
    {
        if (!empty($this->dbmask) and ($this->mask !== $this->dbmask)) {
            return parent::setValue(self::convertToMask($value, $this->dbmask, $this->mask));
        } else {
            return parent::setValue($value);
        }
    }
    
    public function getPostData()
    {
        $value = parent::getPostData();
        
        if (!empty($this->dbmask) and ($this->mask !== $this->dbmask)) {
            return self::convertToMask($value, $this->mask, $this->dbmask);
        } else {
            return $value;
        }
    }

    /**
     * Convert from one mask to another
     * @param string|null $value original date
     * @param string $fromMask source mask
     * @param string $toMask target mask
     * @return original|false|string|null
     */
    public static function convertToMask(?string $value, string $fromMask, string $toMask)
    {
        if ($value) {
            $value = substr($value, 0, strlen($fromMask));
            
            $phpFromMask = str_replace(['dd','mm', 'yyyy'], ['d','m','Y'], $fromMask);
            $phpToMask   = str_replace(['dd','mm', 'yyyy'], ['d','m','Y'], $toMask);
            
            $date = DateTime::createFromFormat($phpFromMask, $value);
            if ($date) {
                return $date->format($phpToMask);
            }
        }
        
        return $value;
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
        $newmask = str_replace('dd', '99', $newmask);
        $newmask = str_replace('mm', '99', $newmask);
        $newmask = str_replace('yyyy', '9999', $newmask);
        
        parent::setMask($newmask);
    }

    /**
     * Set the mask to be used to colect the data
     * @param string $mask
     */
    public function setDatabaseMask(string $mask)
    {
        $this->dbmask = $mask;
    }

    /**
     * Set extra datepicker options (ex: autoclose, startDate, daysOfWeekDisabled, datesDisabled)
     * @param string $option
     * @param string $value
     */
    public function setOption(string $option, string $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Shortcut to convert a date to format yyyy-mm-dd
     * @param string $date = date in format dd/mm/yyyy
     * @return string
     */
    public static function date2us(string $date)
    {
        if ($date) {
            // get the date parts
            $day  = substr($date, 0, 2);
            $mon  = substr($date, 3, 2);
            $year = substr($date, 6, 4);
            return "{$year}-{$mon}-{$day}";
        }
        return '';
    }

    /**
     * Shortcut to convert a date to format dd/mm/yyyy
     * @param $date = date in format yyyy-mm-dd
     * @return string
     */
    public static function date2br(string $date)
    {
        if ($date) {
            // get the date parts
            $year = substr($date, 0, 4);
            $mon  = substr($date, 5, 2);
            $day  = substr($date, 8, 2);
            return "{$day}/{$mon}/{$year}";
        }
        return '';
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
        str_replace('yyyy', 'yy', $this->mask);
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
            TScript::create("tdate_start( '#{$this->id}', '{$this->mask}', '{$language}', '{$outer_size}', '{$options}');");
        }
    }
}
