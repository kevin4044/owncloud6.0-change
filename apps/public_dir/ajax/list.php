<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 19/5/14
 * Time: 上午10:28
 */

// only need filesystem apps
$RUNTIME_APPTYPES=array('filesystem');

// Init owncloud


OCP\JSON::checkLoggedIn();
//init fake root
\OC\Files\Filesystem::getView()->chroot('/Public');

// Load the files
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
$dir = \OC\Files\Filesystem::normalizePath($dir);
if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

$doBreadcrumb = isset($_GET['breadcrumb']);
$data = array();
$baseUrl = OCP\Util::linkTo('public_dir', 'index.php') . '?dir=';


// Make breadcrumb
if($doBreadcrumb) {
    $breadcrumb = \OCA\Files\Helper::makeBreadcrumb($dir);

    $breadcrumbNav = new OCP\Template('files', 'part.breadcrumb', '');
    $breadcrumbNav->assign('breadcrumb', $breadcrumb, false);
    $breadcrumbNav->assign('baseURL', $baseUrl);

    $data['breadcrumb'] = $breadcrumbNav->fetchPage();
}

// make filelist
$files = \OCA\Files\Helper::getFiles($dir);
$files = array_merge($files, PD_Model::get_dir_content($dir));

$user = OC_User::getUser();

//get permission by kevin
if (OC_User::isAdminUser($user)) {
    $permissions = \OCA\Files\Helper::getDirPermissions($dir);
} else {
    $permissions = \OCP\PERMISSION_READ;//read only for all users
    //todo:方法有点太搓了
    foreach ($files as &$file) {
        $file['permissions'] = \OCP\PERMISSION_READ;
    }
}

$list = new OCP\Template("public_dir", "part.list", "");
$list->assign('files', $files, false);
$list->assign('baseURL', $baseUrl, false);
$list->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$list->assign('isPublic', true);
$data['files'] = $list->fetchPage();
$data['permissions'] = $permissions;

OCP\JSON::success(array('data' => $data));
