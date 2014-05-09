<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-7
 * Time: 上午9:47
 */
// Load the files we need
OCP\Util::addStyle('files', 'files');
OCP\Util::addStyle('files', 'upload');

OCP\App::setActiveNavigationEntry('just_index');
// TODO: This dir should be a marco or sth.
\OC\Files\Filesystem::getView()->chroot('/Public');

$dir = isset($_GET['dir']) ? stripslashes($_GET['dir']) : '';
$dir = \OC\Files\Filesystem::normalizePath($dir);
if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
    header('Location: ' . OCP\Util::getScriptName() . '');
    exit();
}
$user = OC_User::getUser();

// Make breadcrumb
$breadcrumb = \OCA\Files\Helper::makeBreadcrumb($dir);
$files = \OCA\Files\Helper::getFiles($dir);
$files = array_merge($files, PD_Model::get_dir_content($dir));


// make breadcrumb und filelist markup
$list = new OCP\Template('files', 'part.list', '');
$list->assign('files', $files);
$list->assign('baseURL', OCP\Util::linkTo('public_dir', 'index.php') . '?dir=');
$list->assign('downloadURL', OCP\Util::linkToRoute('download', array('file' => '/')));
$list->assign('isPublic', true);//TODO: what is it
$breadcrumbNav = new OCP\Template('files', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('public_dir', 'index.php') . '?dir=');

if (OC_User::isAdminUser($user)) {
    $permissions = \OCA\Files\Helper::getDirPermissions($dir);
} else {
    $permissions = \OCP\PERMISSION_READ;//read only for all users
}

// information about storage capacities
$storageInfo=OC_Helper::getStorageInfo($dir);
$maxUploadFilesize=OCP\Util::maxUploadFilesize($dir);
$publicUploadEnabled = \OC_Appconfig::getValue('core', 'shareapi_allow_public_upload', 'yes');
// if the encryption app is disabled, than everything is fine (INIT_SUCCESSFUL status code)
$encryptionInitStatus = 2;
if (OC_App::isEnabled('files_encryption')) {
    $session = new \OCA\Encryption\Session(new \OC\Files\View('/'));
    $encryptionInitStatus = $session->getInitialized();
}

$trashEnabled = \OCP\App::isEnabled('files_trashbin');
$trashEmpty = true;
if ($trashEnabled) {
    $trashEmpty = \OCA\Files_Trashbin\Trashbin::isEmpty($user);
}

$isCreatable = \OC\Files\Filesystem::isCreatable($dir . '/');
$fileHeader = (!isset($files) or count($files) > 0);
$emptyContent = ($isCreatable and !$fileHeader) or $ajaxLoad;
//Page print
$tmpl = new OCP\Template('files', 'index', 'user');
$tmpl->assign('fileList', $list->fetchPage());
$tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
$tmpl->assign('dir', $dir);
$tmpl->assign('isCreatable', $isCreatable);
$tmpl->assign('permissions', $permissions);
$tmpl->assign('files', $files);
$tmpl->assign('trash', $trashEnabled);
$tmpl->assign('trashEmpty', $trashEmpty);
$tmpl->assign('uploadMaxFilesize', $maxUploadFilesize);
$tmpl->assign('uploadMaxHumanFilesize', OCP\Util::humanFileSize($maxUploadFilesize));
$tmpl->assign('allowZipDownload', intval(OCP\Config::getSystemValue('allowZipDownload', true)));
$tmpl->assign('usedSpacePercent', (int)$storageInfo['relative']);
$tmpl->assign('isPublic', false);
$tmpl->assign('publicUploadEnabled', $publicUploadEnabled);
$tmpl->assign("encryptedFiles", \OCP\Util::encryptedFiles());
$tmpl->assign("mailNotificationEnabled", \OC_Appconfig::getValue('core', 'shareapi_allow_mail_notification', 'yes'));
$tmpl->assign("allowShareWithLink", \OC_Appconfig::getValue('core', 'shareapi_allow_links', 'yes'));
$tmpl->assign("encryptionInitStatus", $encryptionInitStatus);
$tmpl->assign('disableSharing', false);
$tmpl->assign('ajaxLoad', $ajaxLoad);
$tmpl->assign('emptyContent', $emptyContent);
$tmpl->assign('fileHeader', $fileHeader);

$tmpl->printPage();
