<?php
namespace Adianti\Base\Lib\Widget\Form;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Widget\Base\TElement;
use Adianti\Base\Lib\Widget\Base\TScript;
use Adianti\Base\Lib\Widget\Container\TTable;
use Adianti\Base\Lib\Widget\Container\TTableRow;
use Adianti\Base\Lib\Widget\Util\TImage;
use Exception;

/**
 * Create a field list
 *
 * @version    5.5
 * @package    widget
 * @subpackage form
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TFieldList extends TTable
{
    protected $row_fields;
    protected $body_created;
    protected $detail_row;
    protected $remove_function;
    protected $clone_function;
    /**@var TAction*/
    protected $sort_action;
    protected $sorting;
    protected $fields_properties;
    /**
     * @var string
     */
    protected $tbody_id;
    protected $uniqid;

    public function __construct()
    {
        parent::__construct();
        $this->{'id'}     = 'tfieldlist_' . mt_rand(1000000000, 1999999999);
        $this->{'class'}  = 'tfieldlist';
        
        $this->row_fields = [];
        $this->fields_properties = [];
        $this->body_created = false;
        $this->detail_row = 0;
        $this->sorting = false;
        $this->remove_function = 'ttable_remove_row(this)';
        $this->clone_function  = 'ttable_clone_previous_row(this)';
    }
    
    public function enableSorting()
    {
        $this->sorting = true;
    }

    /**
     * Define the action to be executed when the user sort rows
     * @param TAction $action object
     * @throws Exception
     */
    public function setSortAction(TAction $action)
    {
        if ($action->isStatic()) {
            $this->sort_action = $action;
        } else {
            $string_action = $action->toString();
            throw new Exception(AdiantiCoreTranslator::translate('Action (^1) must be static to be used in ^2', $string_action, __METHOD__));
        }
    }

    /**
     * Set the remove javascript action
     * @param $action
     */
    public function setRemoveFunction($action)
    {
        $this->remove_function = $action;
    }

    /**
     * Set the clone javascript action
     * @param $action
     */
    public function setCloneFunction(string $action)
    {
        $this->clone_function = $action;
    }

    /**
     * Add a field
     * @param string|object $label Field Label
     * @param AdiantiWidgetInterface $field
     * @param array|null $properties
     * @throws Exception
     */
    public function addField(AdiantiWidgetInterface $field, $label = null, array $properties = null)
    {
        if (!$field instanceof TField) {
//            return;
        }
        $name = $field->getName();

        if (isset($this->row_fields[$name]) and substr($name, -2) !== '[]') {
            throw new Exception(AdiantiCoreTranslator::translate('You have already added a field called "^1" inside the form', $name));
        }

        if ($name) {
            $this->row_fields[$name] = $field;
            $this->fields_properties[$name] = $properties;
        }

        if ($label instanceof TLabel) {
            $label_value = $label->getValue();
        } else {
            $label_value = $label;
        }

        if ($label_value) {
            $field->setLabel($label_value);
        }
    }

    /**
     * Add table header
     * @throws Exception
     */
    public function addHeader()
    {
        $section = parent::addSection('thead');
        
        if ($this->row_fields) {
            $row = parent::addRow();
            
            if ($this->sorting) {
                $row->addCell('')->setProperty('style', 'width:20px');
            }
            
            foreach ($this->row_fields as $name => $field) {
                if ($field instanceof THidden) {
                    $cell = $row->addCell('');
                    $cell->{'style'} = 'display:none';
                } else {
                    $cell = $row->addCell(new TLabel($field->getLabel()));
                    $cell->setProperty('id', 'head_'.$field->getName());
                    if (!empty($this->fields_properties[$name])) {
                        foreach ($this->fields_properties[$name] as $property => $value) {
                            $cell->setProperty($property, $value);
                        }
                    }
                }
            }
        }
        
        return $section;
    }

    public function setUniqId($uniqid)
    {
        $this->uniqid = $uniqid;
    }

    public function getUniqId()
    {
        return $this->uniqid ?? mt_rand(1000000, 9999999);
    }

    /**
     * Add detail row
     * @param object $item Data object
     * @return TTableRow
     * @throws Exception
     */
    public function addDetail(object $item)
    {
        if (!$this->body_created) {
            $body = parent::addSection('tbody');
            $this->tbody_id = 'tbody_' . $this->getUniqId();
            $body->{'id'} = $this->tbody_id;
            $this->body_created = true;
        }
        
        if ($this->row_fields) {
            $row = parent::addRow();
            $row->setProperty('id', $this->getUniqId());
            if ($this->sorting) {
                $move = new TImage('fa:arrows gray');
                $move->{'class'} .= ' handle';
                $move->{'style'} .= ';font-size:100%;cursor:move';

                $style_value = 'width:20px;vertical-align: bottom;padding-bottom: 5px;';
                $row->addCell($move)->setProperty('style', $style_value);
            }
            
            foreach ($this->row_fields as $field) {
                if ($this->detail_row == 0) {
                    $clone = $field;
                } else {
                    $clone = clone $field;
                }
                
                $name  = str_replace(['[', ']'], ['', ''], $field->getName());
                $clone->setId($name.'_'.$this->getUniqId());
                $clone->{'data-row'} = $this->detail_row;

                ob_start();
                $clone->show();
                $html = ob_get_contents();
                ob_clean();

                $component_html = '<div>'.$clone->getLabel(). $html .'</div>';
                $cell = $row->addCell($component_html);
                $cell->setProperty('id', 'td_'.$name.'_'.$this->getUniqId());
                if ($clone instanceof THidden) {
                    $cell->{'style'} = 'display:none';
                }
                
                if (!empty($item->$name) or (isset($item->$name) and $item->$name == '0')) {
                    $clone->setValue($item->$name);
                } else {
                    $clone->setValue(null);
                }
            }
            
            $del = new TElement('div');
            $del->{'class'} = 'btn btn-default btn-sm';
            $del->{'style'} = 'padding:3px 7px';
            $del->{'onclick'} = $this->remove_function;
            $del->add('<i class="fa fa-times red"></i>');
            
            $row->addCell($del)->setProperty('style', 'width:20px;vertical-align: bottom');
        }
        $this->detail_row ++;
        
        return $row;
    }
    
    /**
     * Add clone action
     */
    public function addCloneAction()
    {
        parent::addSection('tfoot');
        
        $row = parent::addRow();
        
        if ($this->sorting) {
            $row->addCell('');
        }
        
        if ($this->row_fields) {
            /**@var AdiantiWidgetInterface $field*/
            foreach ($this->row_fields as $field) {
                $cell = $row->addCell('');
                $cell->setProperty('id', 'foot_'.$field->getId());
                if ($field instanceof THidden) {
                    $cell->{'style'} = 'display:none';
                }
            }
        }
        
        $add = new TElement('div');
        $add->{'class'} = 'btn btn-default btn-sm';
        $add->{'style'} = 'padding:3px 7px';
        $add->{'onclick'} = $this->clone_function;
        $add->{'onclick'} = $this->getCloneFunction();
        $add->add('<i class="fa fa-plus green"></i>');
        
        // add buttons in table
        $row->addCell($add);
    }

    /**
     * Clear field list
     * @param string $name field list name
     */
    public static function clear(string $name)
    {
        TScript::create("tfieldlist_clear('{$name}');");
    }

    /**
     * Clear some field list rows
     * @param string $name field list name
     * @param int $start
     * @param int $length
     */
    public static function clearRows(string $name, int $start = 0, int $length = 0)
    {
        TScript::create("tfieldlist_clear_rows('{$name}', {$start}, {$length});");
    }

    /**
     * Clear some field list rows
     * @param string $name field list name
     * @param $rows
     */
    public static function addRows(string $name, $rows)
    {
        TScript::create("tfieldlist_add_rows('{$name}', {$rows});");
    }
    
    /**
     * Show component
     */
    public function show()
    {
        parent::show();
        $id = $this->{'id'};
        
        if ($this->sorting) {
            if (empty($this->sort_action)) {
                TScript::create("ttable_sortable_rows('{$id}', '.handle')");
            } else {
                if (!empty($this->row_fields)) {
                    $first_field = array_values($this->row_fields)[0];
                    $this->sort_action->setParameter('static', '1');
                    $form_name   = $first_field->getFormName();
                    $string_action = $this->sort_action->serialize(false);
                    $sort_action = "function() { __adianti_post_data('{$form_name}', '{$string_action}'); }";
                    TScript::create("ttable_sortable_rows('{$id}', '.handle', $sort_action)");
                }
            }
        }
    }

    protected function getCloneFunction()
    {
        return "ttable_clone_previous_row2(this, '".$this->tbody_id."')";
    }
}
