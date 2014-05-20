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

$dir = $_POST['dir'];
$folder = $_POST['foldername'];

$full_dir_name = PD_Model::get_full_dir($folder);

if (false === mkdir($full_dir_name)) {

    //failed with logs
} else {

    //success
}

