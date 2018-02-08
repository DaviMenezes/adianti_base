<?php
namespace Adianti\Base\Lib\Validator;

use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Required field validation
 *
 * @version    5.0
 * @package    validator
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TRequiredValidator extends TFieldValidator
{
    /**
     * Validate a given value
     * @param $label Identifies the value to be validated in case of exception
     * @param $value Value to be validated
     * @param $parameters aditional parameters for validation
     */
    public function validate($label, $value, $parameters = null)
    {
        if ((is_null($value)) or (is_scalar($value) and !is_bool($value) and trim($value)=='') or (is_array($value) and count($value)==1 and isset($value[0]) and empty($value[0])) or (is_array($value) and empty($value))) {
            throw new Exception(AdiantiCoreTranslator::translate('The field ^1 is required', $label));
        }
    }
}
