<?php
namespace Adianti\Base\Modules\Admin\Model;

use Adianti\Base\Lib\Core\AdiantiApplicationConfig;
use Adianti\Base\Lib\Registry\TSession;
use App\Http\Middleware\ProgramPermissionMiddleware;

/**
 * SystemPermission
 *
 * @version    1.0
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SystemPermission
{
    public static function checkPermission($action)
    {
        $programs = TSession::getValue('programs');

        $public = ProgramPermissionMiddleware::getDefaultPermissions();

        $all = array_merge($programs, $public);

        return in_array($action, $all);
    }
}
