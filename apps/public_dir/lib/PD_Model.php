<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-8
 * Time: 上午11:57
 */


class PD_Model {
    static private $DB_NAME = '`*PREFIX*public_map`';
    static public function setinto_db($data_dir, $dst_dir, $user, $filename) {
        $query = OC_DB::prepare('INSERT INTO '.self::$DB_NAME.
            '(`uid`, `file_name`, `src_dir`, `public_dir`) '.
            'VALUES (?,?,?,?)');
        return $query->execute(array($user,$filename,$data_dir,$dst_dir));
    }

    /**
     * @param $dir public_dir
     */

    static public function get_dir_content($dir)
    {
        $query = OC_DB::prepare('select file_name,src_dir from '.self::$DB_NAME.' where dst_dir='.$dir);
        $result = $query->execute();

        $user = OC_User::getUser();
        \OC\Files\Filesystem::getView()->chroot('/'.$user);

        while ($row = $result->fetchRow()) {

        }

    }
}