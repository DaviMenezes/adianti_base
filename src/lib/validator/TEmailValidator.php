<?php
namespace Adianti\Base\Lib\Validator;

use Adianti\Base\Lib\Validator\TFieldValidator;
use Adianti\Base\Lib\Core\AdiantiCoreTranslator;
use Exception;

/**
 * Email validation
 *
 * @version    5.5
 * @package    validator
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class TEmailValidator extends TFieldValidator
{
    /**
     * Validate a given value
     * @param $label Identifies the value to be validated in case of exception
     * @param $value Value to be validated
     * @param $parameters aditional parameters for validation
     */
    public function validate(string $label, string $value, array $parameters = null)
    {
        if (!empty($value)) {
            $filter = filter_var(trim($value), FILTER_VALIDATE_EMAIL);
            
            if ($filter === false) {
                throw new Exception(AdiantiCoreTranslator::translate('The field ^1 contains an invalid e-mail', $label));
            }
        }
    }
}
