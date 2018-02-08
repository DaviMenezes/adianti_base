<?php
namespace Adianti\Base\Lib\Validator;

use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Maximum value validation
 *
 * @version    5.0
 * @package    validator
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TMaxValueValidator extends TFieldValidator
{
    /**
     * Validate a given value
     * @param $label Identifies the value to be validated in case of exception
     * @param $value Value to be validated
     * @param $parameters aditional parameters for validation (max value)
     */
    public function validate($label, $value, $parameters = null)
    {
        $maxvalue = $parameters[0];
        
        if ($value > $maxvalue) {
            throw new Exception(AdiantiCoreTranslator::translate('The field ^1 can not be greater than ^2', $label, $maxvalue));
        }
    }
}
