<?php
namespace Adianti\Base\Lib\Validator;

use Adianti\Base\Lib\Validator\TFieldValidator;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Numeric validation
 *
 * @version    5.5
 * @package    validator
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TNumericValidator extends TFieldValidator
{
    /**
     * Validate a given value
     * @param $label Identifies the value to be validated in case of exception
     * @param $value Value to be validated
     * @param $parameters aditional parameters for validation (min value)
     */
    public function validate($label, $value, array $parameters = NULL)
    {
        if (!is_numeric($value))
        {
            throw new Exception(AdiantiCoreTranslator::translate('The field ^1 must be numeric', $label));
        }
    }
}
