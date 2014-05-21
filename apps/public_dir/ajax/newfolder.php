<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 20/5/14
 * Time: 下午8:14
 */

$user = OC_User::getUser();
if (!OC_User::isAdminUser($user)) {
    //erro
    exit;
}

\OC\Files\Filesystem::getView()->chroot('/public');

$dir = $_POST['dir'];
$folder = $_POST['foldername'];

$target = $dir . '/' . stripslashes($folder);
if (\OC\Files\Filesystem::file_exists($target)) {
    $result['data'] = array('message' => $l10n->t(
            'The name %s is already used in the folder %s. Please choose a different name.',
            array($foldername, $dir))
    );
    OCP\JSON::error($result);
    exit();
}
if(\OC\Files\Filesystem::mkdir($target)) {
    if ( $dir !== '/') {
        $path = $dir.'/'.$foldername;
    } else {
        $path = '/'.$foldername;
    }
    $meta = \OC\Files\Filesystem::getFileInfo($path);
    $id = $meta['fileid'];
    OCP\JSON::success(array('data' => array('id' => $id)));
    exit();
} else {
    \OCP\JSON::error(array('data' =>array('message'=>'failed')));
}

/*$full_dir_name = PD_Model::get_full_dir($folder);
if (false === mkdir($full_dir_name)) {

    \OCP\JSON::error(array('message'=>'failed'));
    //failed with logs
} else {
    \OCP\JSON::success();
    //success
}*/

