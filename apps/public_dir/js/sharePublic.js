/**
 * Created by wangjunlong on 14-5-7.
 */

var PublicShare = {
    dropdownshown:false,
    submit:function (itemType, src_dir, filename){
        var dst_dir = $('#data-dst-dir').val();
        console.log('filename');
        console.log(filename);
        $.post(
            OC.filePath('public_dir', 'ajax', 'link.php'),
            {
                data_dir:src_dir,
                data_type:itemType,
                dst_dir:dst_dir,
                file_name:filename
            },
            function(data) {
                console.log(data);
            }
        );
    },
    showDropDown:function (itemType, appendTo, src_dir,filename) {

        if (this.dropdownshown === true) {
            $('div#dropdown').remove();
            this.dropdownshown = false;
            return;
        }

        var html = '<div id="dropdown" class="drop" data-item-type="'
            +itemType+'" data-item-dir="'+src_dir+'">';
        var dropDownEl;

        html += '<form >'
        html += '<label for="data-dst-dir">目标文件夹</label>';
        html += '</br>';
        html += '<input id="data-dst-dir" type="text" />';
        html += '<img id="dir-toggle" class="svg" src="/core/img/actions/caret.png"/>';

        html += '<br/>';
        html += '<textarea style="display: none" id="dir-selecter"></textarea>';
        html += '<button id="share_public" type="button">共享至资料库</button>';

        html += '</form>';

        html += '</div>';

        dropDownEl = $(html);
        dropDownEl.appendTo(appendTo);

        $('#dir-toggle').click(function(){
            $('#dir-selecter').toggle();
        })

        $('#share_public').click(function() {
            PublicShare.submit(itemType,src_dir,filename);
        });


        this.dropdownshown = true;
    }
}


$(document).ready(function (){
    var sharePublic = $('#sharePublic').data('status');

    console.log('disablePublic');
    console.log(sharePublic);
    if (typeof FileActions !== 'undefined'
        && sharePublic === true) {
        FileActions.register('all',
            'public',
            OC.PERMISSION_READ,
            OC.imagePath('core', 'actions/share'),
            function(filename) {
                if ($('#dir').val() == '/') {
                    var src_dir = $('#dir').val();
                } else {
                    var src_dir = $('#dir').val() + '/';
                }
                var tr = FileList.findFileEl(filename);
                if ($(tr).data('type') == 'dir') {
                    var itemType = 'folder';
                } else {
                    var itemType = 'file';
                }
                var appendTo = $(tr).find('td.filename');

                PublicShare.showDropDown(itemType, appendTo, src_dir, filename);
            }
        )

        //first cancle default download function
/*        unset(FileActions.actions['all']['Download']);
        FileActions.register('file', 'Download',OC.PERMISSION_READ
            , function() {
                return OC.imagePath('core', 'actions/download');
            },function(filename) {
                alert(filename);
            }
        );*/

    } else {
        //仅仅显示在
        FileActions.register('all', 'Public_download',OC.PERMISSION_READ
            , OC.imagePath('core', 'actions/download')
            ,function(filename) {
                var tr = FileList.findFileEl(filename);
                var user_name = $(tr).attr('data-owner');
                window.location = OC.filePath('public_dir', '', 'download.php') + '?file=' + encodeURIComponent(filename) + '&user_name=' + encodeURIComponent(user_name);
            }
        );
    }
});