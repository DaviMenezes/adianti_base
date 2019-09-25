<?php

namespace Adianti\Base\Modules\Admin\User\Service;

use Adianti\Base\Lib\Control\TAction;
use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Lib\Widget\Dialog\TMessage;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Dvi\Support\Http\Request;
use Dvi\Adianti\Database\Transaction;
use Dvi\Adianti\Helpers\Redirect;

/**
 * Serivce ProfilePhotoRemove
 *
 * @package    Serivce
 * @subpackage User
 * @author     Davi Menezes
 * @copyright  Copyright (c) 2018. (davimenezes.dev@gmail.com)
 * @see https://github.com/DaviMenezes
 */
class ProfilePhotoRemove
{
    public function removeProfileImage(Request $request)
    {
        try {
            Transaction::open();

            $user = SystemUser::find(TSession::getValue('userid'));
            if (!empty($user->profile_image)) {
                $image = $user->profile_image;
                $file_name = str($image)->lastStr('/');
                $path = str($image)->removeRight($file_name);
                $image_path = PATH . '/' . $image;
                unlink($image_path);
                if (count(scandir($path)) == 2) {
                    rmdir($path);
                }

                $user->profile_image = null;
                $user->store();

                TSession::setValue('profile_image', null);

                new TMessage('info', 'Imagem removida');

                Redirect::redirectToRoute(route('/admin/system/profile/form/edit'));
            }
            Transaction::close();
        } catch (\Exception $e) {
            Transaction::rollback();
            new TMessage('error', $e->getMessage());
        }
    }

    public function redirect($param)
    {

    }
}
