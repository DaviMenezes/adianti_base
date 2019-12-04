<?php
namespace Adianti\Base\Lib\Widget\Wrapper;

use Adianti\Base\Lib\Base\TStandardSeek;
use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Adianti\Base\Lib\Database\TCriteria;
use Adianti\Base\Lib\Database\TTransaction;
use Adianti\Base\Lib\Widget\Form\TSeekButton;
use Exception;

/**
 * Abstract Record Lookup Widget: Creates a lookup field used to search values from associated entities
 *
 * @version    5.5
 * @package    widget
 * @subpackage wrapper
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TDBSeekButton extends TSeekButton
{
    /**
     * Class Constructor
     * @param string $name name of the form field
     * @param string $database name of the database connection
     * @param string $form name of the parent form
     * @param string $model name of the Active Record to be searched
     * @param string $display_field name of the field to be searched and shown
     * @param string $receive_key name of the form field to receive the primary key
     * @param string $receive_display_field name of the form field to receive the "display field"
     * @param TCriteria|null $criteria
     * @param string $operator
     * @throws Exception
     */
    public function __construct($name, $database, $form, $model, $display_field, $receive_key = null, $receive_display_field = null, TCriteria $criteria = null, $operator = 'like')
    {
        parent::__construct($name);

        if (empty($database)) {
            throw new Exception(\Adianti\Core\AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'database', __CLASS__));
        }

        if (empty($model)) {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'model', __CLASS__));
        }

        if (empty($display_field)) {
            throw new Exception(AdiantiCoreTranslator::translate('The parameter (^1) of ^2 is required', 'display_field', __CLASS__));
        }

        $obj  = new TStandardSeek;
        $ini  = \Adianti\Core\AdiantiApplicationConfig::get();
        $seed = APPLICATION_NAME . (!empty($ini['general']['seed']) ? $ini['general']['seed'] : 's8dkld83kf73kf094');

        // define the action parameters
        $action = new TAction(array($obj, 'onSetup'));
        $action->setParameter('hash', md5("{$seed}{$database}{$model}{$display_field}"));
        $action->setParameter('database', $database);
        $action->setParameter('parent', $form);
        $action->setParameter('model', $model);
        $action->setParameter('display_field', $display_field);
        $action->setParameter('receive_key', !empty($receive_key) ? $receive_key : $name);
        $action->setParameter('receive_field', !empty($receive_display_field) ? $receive_display_field : null);
        $action->setParameter('criteria', base64_encode(serialize($criteria)));
        $action->setParameter('operator', ($operator == 'ilike') ? 'ilike' : 'like');
        $action->setParameter('mask', '');
        $action->setParameter('label', AdiantiCoreTranslator::translate('Description'));
        parent::setAction($action);
    }

    /**
     * Set search criteria
     */
    public function setCriteria(TCriteria $criteria)
    {
        $this->getAction()->setParameter('criteria', base64_encode(serialize($criteria)));
    }

    /**
     * Set operator
     */
    public function setOperator($operator)
    {
        $this->getAction()->setParameter('operator', ($operator == 'ilike') ? 'ilike' : 'like');
    }

    /**
     * Set display mask
     * @param $mask Display mask
     */
    public function setDisplayMask($mask)
    {
        $this->getAction()->setParameter('mask', $mask);
    }

    /**
     * Set display label
     * @param $mask Display label
     */
    public function setDisplayLabel($label)
    {
        $this->getAction()->setParameter('label', $label);
    }

    public function setValue(?string $value)
    {
        parent::setValue($value);

        if (!empty($this->auxiliar)) {
            $database = $this->getAction()->getParameter('database');
            $model    = $this->getAction()->getParameter('model');
            $mask     = $this->getAction()->getParameter('mask');
            $display_field = $this->getAction()->getParameter('display_field');

            if (!empty($value)) {
//                \Adianti\Database\TTransaction::open($database);
                $activeRecord = new $model($value);

                if (!empty($mask)) {
                    $this->auxiliar->setValue($activeRecord->render($mask));
                } elseif (isset($activeRecord->$display_field)) {
                    $this->auxiliar->setValue($activeRecord->$display_field);
                }
//                TTransaction::close();
            }
        }
    }
}
