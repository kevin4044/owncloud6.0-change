<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-7
 * Time: 上午9:36
 */
OC_Util::addScript('public_dir', 'sharePublic');
OC_App::addNavigationEntry( array(
    'id' => 'just_index',
    'order' => 2,
    'href' => OCP\Util::linkTo( 'public_dir', 'index.php' ),
    "icon" => OCP\Util::imagePath("core", "places/files.svg"),
    'name' => 'Just file'));
