<?php
/**
 * Created by PhpStorm.
 * User: wangjunlong
 * Date: 14-5-7
 * Time: 上午9:47
 */

$file_list = PD_Filesys::get_file_content('/');

$file_list = process_file_list($file_list);


show_files($file_list);

function process_file_list($file_list)
{
    if (OC_User::isLoggedIn()) {
        $user = OC_User::getUser();
    } else {
        $user = null;
    }

    foreach ($file_list as &$file) {
        //todo:add mimetype icon
        $file['writable'] = ($user == $file['uid']) && $file['is_editable'];
        $file['mimetype'] = 'doc';
        $file['icon'] = '/core/img/filetypes/folder.png';
    }

    return $file_list;
}

function show_files($file_list)
{
    $list_view = new OCP\Template('public_dir', 'part.list', '');
    $list_view->assign('files', $file_list);
    $list_view->printPage();
// Make breadcrumb
    $breadcrumb = \OCA\Files\Helper::makeBreadcrumb('/');
    $breadcrumbNav = new OCP\Template('files', 'part.breadcrumb', '');
    $breadcrumbNav->assign('breadcrumb', $breadcrumb);
    $breadcrumbNav->assign('baseURL', OCP\Util::linkTo('public_dir', 'index.php') . '?dir=');

    $tmpl = new \OCP\Template('public_dir', 'index.php', '');
    $tmpl->assign('fileList', $list_view->fetchPage());
    $tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
    $tmpl->printPage();
}
