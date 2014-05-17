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
$user_name = $_GET["user_name"];

$filepath = OC::$SERVERROOT.'/data'.'/'.$user_name.'/files/'.$filename;

if (!file_exists($filepath)) {
    header("HTTP/1.0 404 Not Found");
    $tmpl = new OCP\Template( '', '404', 'guest' );
    $tmpl->assign('file', $filename);
    $tmpl->printPage();
    exit;
}

$file = fopen($filepath, "r");
Header ( "Content-type: application/octet-stream" );
Header ( "Accept-Ranges: bytes" );
Header ( "Accept-Length: " . filesize($filepath) );
Header ( "Content-Disposition: attachment; filename=" . $filename );

echo fread($file,filesize($filepath));
fclose($file);
exit();
