<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-7
 * Time: 下午12:09
 */

// don't block php session during download
session_write_close();

$filename = $_GET["file"];

if(!\OC\Files\Filesystem::file_exists($filename)) {
    header("HTTP/1.0 404 Not Found");
    $tmpl = new OCP\Template( '', '404', 'guest' );
    $tmpl->assign('file', $filename);
    $tmpl->printPage();
    exit;
}

$ftype=\OC\Files\Filesystem::getMimeType( $filename );

header('Content-Type:'.$ftype);
OCP\Response::setContentDispositionHeader(basename($filename), 'attachment');
OCP\Response::disableCaching();
header('Content-Length: '.\OC\Files\Filesystem::filesize($filename));

OC_Util::obEnd();
\OC\Files\Filesystem::readfile( $filename );
