<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-8
 * Time: 上午11:57
 */


class PD_Model {
    static private $DB_NAME = '`*PREFIX*public_map`';

    static public function get_full_dir($dir)
    {
        return OC::$SERVERROOT.'/data/public'.$dir;
    }

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
        $query = OC_DB::prepare('select uid,file_name,src_dir from '.self::$DB_NAME.' where `public_dir` = ?');
        $result = $query->execute(array($dir));
        $files = array();


        while ($row = $result->fetchRow()) {
            $file_name = $row['file_name'];
            $src_dir = $row['src_dir'];
            $user = $row['uid'];
            $file = self::get_file_content($user, $src_dir, $file_name);
            if ($file !== false) {
                $files[] = $file;
            }
        }
        return $files;

    }

    /**
     * @param $user
     * @param $src_dir
     * @param $file_name
     * @return result
     */
    private static function get_file_content($user, $src_dir, $file_name)
    {
        $ret = array();

        \OC\Files\Filesystem::getView()->chroot('/' . $user);
        $path = \OC\Files\Filesystem::normalizePath(\OC\Files\Filesystem::getView()->getRoot() . '/files/' . $src_dir);

        /**
         * @var \OC\Files\Storage\Storage $storage
         * @var string $internalPath
         */
        list($storage, $internalPath) = \OC\Files\Filesystem::resolvePath($path);
        if ($storage) {
            $cache = $storage->getCache($internalPath);

            //check if dir is scan completed
            if ($cache->getStatus($internalPath) < \OC\Files\Cache\Cache::COMPLETE) {
                $scanner = $storage->getScanner($internalPath);
                $scanner->scan($internalPath, \OC\Files\Cache\Scanner::SCAN_SHALLOW);
            } else {
                $watcher = $storage->getWatcher($internalPath);
                $watcher->checkUpdate($internalPath);
            }
            $fileid = $cache->getId($internalPath);
            //fetch file from cache
            if ($fileid > -1) {
                $sql = 'SELECT `fileid`, `storage`, `path`, `parent`, `name`, `mimetype`, `mimepart`, `size`, `mtime`,
						   `storage_mtime`, `encrypted`, `unencrypted_size`, `etag`
					FROM `*PREFIX*filecache` WHERE `parent` = ? and `name` = ? ORDER BY `name` ASC';
                $result = OC_DB::executeAudited($sql, array($fileid, $file_name));
                $file = $result->fetchRow();
                $file['mimetype'] = $cache->getMimetype($file['mimetype']);
                $file['mimepart'] = $cache->getMimetype($file['mimepart']);
                if ($file['storage_mtime'] == 0) {
                    $file['storage_mtime'] = $file['mtime'];
                }
                if ($file['encrypted']
                    or ($file['unencrypted_size'] > 0
                        and $file['mimetype'] === 'httpd/unix-directory')) {
                    $file['encrypted_size'] = $file['size'];
                    $file['size'] = $file['unencrypted_size'];
                }
                $file['type'] = 'file';
                $file['permission'] = \OCP\PERMISSION_READ;
                $file['base'] = $file_name;
                $file['date'] = \OCP\Util::formatDate($file['mtime']);
                if ($file['type'] === 'file') {
                    $fileinfo = pathinfo($file['name']);
                    $file['basename'] = $fileinfo['filename'];
                    if (!empty($fileinfo['extension'])) {
                        $file['extension'] = '.' . $fileinfo['extension'];
                    } else {
                        $file['extension'] = '';
                    }
                }
                $file['directory'] = $src_dir;
                $file['isPreviewAvailable'] = \OC::$server->getPreviewManager()->isMimeSupported($file['mimetype']);
                $file['icon'] = \OCA\Files\Helper::determineIcon($file);
                $file['permissions'] = \OCP\PERMISSION_READ;
                $file['owner'] = $user;
                $ret = $file;

            }
        }
        return $ret;
    }
}