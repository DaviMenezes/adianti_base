<?php

namespace Adianti\Base\Modules\Admin\User\Service;

use Adianti\Base\Lib\Registry\TSession;
use Adianti\Base\Modules\Admin\User\Model\SystemUser;
use Dvi\Support\Http\Request;
use Dvi\Support\Service\Database\Transaction;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service ProfilePhotoUploadService
 *
 * @package    Service
 * @subpackage Contact
 * @author     Davi Menezes
 * @copyright  Copyright (c) 2018. (davimenezes.dev@gmail.com)
 * @see https://github.com/DaviMenezes
 */
class ProfilePhotoUploadService
{
    public function show(Request $request)
    {
        $content_type_list = array();
        $content_type_list['png']  = 'image/png';
        $content_type_list['jpg']  = 'image/jpeg';

        $response = array();

        $folder = 'files/users/'.TSession::getValue('userid');
        if (!is_writable('files/users')) {
            $response['type'] = 'error';
            $response['msg'] = "Permission denied: {$folder}";
            echo json_encode($response);
            return;
        }

        /**@var UploadedFile $file*/
        $file = $request->obj()->files->get('fileName');
        if ($file->isValid()) {
            $path = $folder.'/'.$file->getClientOriginalName();
            if (!in_array($file->getMimeType(), array_values($content_type_list))) {
                $response = array();
                $response['type'] = 'error';
                $response['msg'] = "Extension not allowed";
                echo json_encode($response);
                return;
            }

            try {
                if (is_dir($folder)) {
                    $files = scandir($folder);
                    if (count($files) > 2) {
                        unset($files[0], $files[1]);
                        foreach ($files as $item) {
                            unlink($folder.'/'.$item);
                        }
                    }
                }
                $file->move($folder, $file->getClientOriginalName());
                $response['type'] = 'success';
                $response['fileName'] = $file->getClientOriginalName();

                Transaction::open();
                $user = SystemUser::find(TSession::getValue('userid'));
                $user->profile_image = $path;
                $user->store();

                Transaction::close();

                $result = json_encode($response);
                echo $result;
            } catch (\Exception $e) {
                Transaction::rollback();
                unlink($path);
                $response['type'] = 'error';
                $response['msg'] = $e->getMessage();
                echo json_encode($response);
            }
        }
    }
}
