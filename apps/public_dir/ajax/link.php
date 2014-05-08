<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-8
 * Time: 上午11:48
 */
OCP\User::checkLoggedIn();
$ret = true;
$res = array(
    'errno' =>0,
    'message'=>''
);

$data_dir = $_POST['data_dir'];
$data_type = $_POST['data_type'];
$dst_dir = $_POST['dst_dir'];
$file_name = $_POST['file_name'];

$user = OC_User::getUser();

if ($data_type != 'dir') {
    //todo:check if file already exist
    if (PD_Model::setinto_db($data_dir,$dst_dir,$user,$file_name)
        === false) {
        $res['errno'] = -1;
        $res['message'] = 'set into db erro';
    }
} else {
    //TODO:find dircontents and create dir, and move files into dir;
}


OCP\JSON::encodedPrint($res);



