<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-7
 * Time: 下午12:09
 */

// don't block php session during download
session_write_close();

\OC\Files\Filesystem::getView()->chroot('/Public');

$filename = $_GET["file"];
$user_name = isset($_GET["user_name"])?$_GET["user_name"]:null;

if ($user_name == 'undefined') {
    $filepath = file_path_gen($filename);
} else {
    $filepath = file_path_gen($filename, $user_name);
}

if (!file_exists($filepath)) {
    header("HTTP/1.0 404 Not Found");
    $tmpl = new OCP\Template( '', '404', 'guest' );
    $tmpl->assign('file', $filename);
    $tmpl->printPage();
    exit;
}

$xsendfile = false;
if (isset($_SERVER['MOD_X_SENDFILE_ENABLED']) ||
    isset($_SERVER['MOD_X_SENDFILE2_ENABLED']) ||
    isset($_SERVER['MOD_X_ACCEL_REDIRECT_ENABLED'])) {
    $xsendfile = true;
}

if (!is_dir($filepath)) {
    $file = fopen($filepath, "r");
    Header ( "Content-type: application/octet-stream" );
    Header ( "Accept-Ranges: bytes" );
    Header ( "Accept-Length: " . filesize($filepath) );
    Header ( "Content-Disposition: attachment; filename=" . $filename );

    echo fread($file,filesize($filepath));
    fclose($file);
} else {
    $dir_name = $filename;
    valid_zip_download();
    $executionTime = intval(ini_get('max_execution_time'));
    set_time_limit(0);
    $zip = new ZipArchive();
    $zip_name = OC_Helper::tmpFile('.zip');
    if ($zip->open($zip_name, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE)!==true) {
        $l = OC_L10N::get('lib');
        throw new Exception($l->t('cannot open "%s"', array($zip_name)));
    }
    $zip = zip_add_dir($dir_name, $zip);
    $zip->close();
    set_time_limit($executionTime);

    ini_set('zlib.output_compression', 'off');
    Header ( "Content-type: application/zip" );
    Header ( "Content-Length: " . filesize($zip_name) );
    OC_Files::addSendfileHeader($zip_name);

    $handle = fopen($zip_name, 'r');
    echo fread($handle, filesize($zip_name));
}
exit();

function zip_add_dir($dir,&$zip)
{
    \OC\Files\Filesystem::getView()->chroot('/Public');
    $filelist = OC_Files::getDirectoryContent($dir);
    foreach($filelist as $file) {
        $filename=$file['name'];
        $file=$dir.'/'.$filename;
        if(\OC\Files\Filesystem::is_file($file)) {
            $tmpFile=\OC\Files\Filesystem::toTmpFile($file);
            OC_Files::$tmpFiles[]=$tmpFile;
            $zip->addFile($tmpFile);
        }elseif(\OC\Files\Filesystem::is_dir($file)) {
            $zip = zip_add_dir($file, $zip);
        }
    }

    $link_filelist = PD_Model::get_dir_content($dir);
    foreach ($link_filelist as $file) {
        $each_file = file_path_gen($file['file_name'], $file['owner']);
        $zip->addFile($each_file);
    }
    return $zip;
}


function valid_zip_download()
{
    if (!OC_Config::getValue('allowZipDownload', true)) {
        $l = OC_L10N::get('lib');
        header("HTTP/1.0 409 Conflict");
        OC_Template::printErrorPage(
            $l->t('ZIP download is turned off.'),
            $l->t('Files need to be downloaded one by one.')
            . '<br/><a href="javascript:history.back()">' . $l->t('Back to Files') . '</a>'
        );
        exit;
    }
}
function file_path_gen($filename, $user_name="")
{
    if ($user_name === "") {
        return OC::$SERVERROOT.'/data/public/'.$filename;
    } else {
        return OC::$SERVERROOT.'/data'.'/'.$user_name.'/files/'.$filename;
    }
}
