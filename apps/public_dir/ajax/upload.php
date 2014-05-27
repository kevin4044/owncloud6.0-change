<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 22/5/14
 * Time: 下午10:01
 */
$l = OC_L10N::get('files');

$dir = isset($_POST['dir']) ? $_POST['dir'] : "";

if (!$dir || empty($dir) || $dir === false) {
    OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('Unable to set upload directory.')))));
    die();
}
if (!isset($_FILES['files'])) {
    OCP\JSON::error(array('data' => array_merge(array('message' => $l->t('No file was uploaded. Unknown error')), $storageStats)));
    exit();
}
foreach ($_FILES['files']['error'] as $error) {
    if ($error != 0) {
        $errors = array(
            UPLOAD_ERR_OK => $l->t('There is no error, the file uploaded with success'),
            UPLOAD_ERR_INI_SIZE => $l->t('The uploaded file exceeds the upload_max_filesize directive in php.ini: ')
                . ini_get('upload_max_filesize'),
            UPLOAD_ERR_FORM_SIZE => $l->t('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
            UPLOAD_ERR_PARTIAL => $l->t('The uploaded file was only partially uploaded'),
            UPLOAD_ERR_NO_FILE => $l->t('No file was uploaded'),
            UPLOAD_ERR_NO_TMP_DIR => $l->t('Missing a temporary folder'),
            UPLOAD_ERR_CANT_WRITE => $l->t('Failed to write to disk'),
        );
        OCP\JSON::error(array('data' => array_merge(array('message' => $errors[$error]), $storageStats)));
        exit();
    }
}

$files = $_FILES['files'];

$storageStats = \OCA\Files\Helper::buildFileStorageStatistics($dir);
$maxUploadFileSize = $storageStats['uploadMaxFilesize'];
$maxHumanFileSize = OCP\Util::humanFileSize($maxUploadFileSize);

$totalSize = 0;
foreach ($files['size'] as $size) {
    $totalSize += $size;
}
if ($maxUploadFileSize >= 0 and $totalSize > $maxUploadFileSize) {
    OCP\JSON::error(array('data' => array('message' => $l->t('Not enough storage available'),
        'uploadMaxFilesize' => $maxUploadFileSize,
        'maxHumanFilesize' => $maxHumanFileSize)));
    exit();
}

\OC\Files\Filesystem::getView()->chroot('/public');


if (strpos($dir, '..') === false) {
    foreach ($files as $file) {
        $target = OCP\Files::buildNotExistingFileName(stripslashes($dir), $files['name'][$i]);
        OC_Log::write('public_dir', 'up_load file: target='.$target,OC_Log::WARN);
        if (!\OC\Files\Filesystem::file_exists($target)) {
        }
    }

} else {
    $error = $l->t('Invalid directory.');
    OCP\JSON::error(array(array('data' => array_merge(array('message' => $error), $storageStats))));
    exit();
}
