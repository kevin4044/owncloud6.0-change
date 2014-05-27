<?php
/**
 * Created by PhpStorm.
 * User: weroadshowdev
 * Date: 26/5/14
 * Time: 下午5:34
 */

class PD_Filesys {
    static private $DB_NAME = '`*PREFIX*public_map`';
    static private function get_id_by_name($dir_name)
    {
        if ($dir_name == '/') {
            return 0;
        }

        $query = OC_DB::prepare('select id from '. self::$DB_NAME. ' where filename=? limit 1');
        $ret = $query->execute(array($dir_name));

        return $ret['id'];
    }

    static public function get_file_content($dir)
    {
        $file_list = array();
        $dir_id = self::get_id_by_name($dir);


        $query = OC_DB::prepare('select * from '.self::$DB_NAME.
            'where parent_id=?');
        $ret = $query->execute(array($dir_id));

        while ($file = $ret->fetchRow()) {
            $file_list[] = $file;
        }

        return $file_list;
    }
} 