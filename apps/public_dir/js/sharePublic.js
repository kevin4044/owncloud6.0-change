/**
 * Created by wangjunlong on 14-5-7.
 */

var PublicShare = {
    dropdownshown:false,
    showDropDown:function (itemType, appendTo, src_dir) {

        if (this.dropdownshown === true) {
            $('div#dropdown').remove();
            this.dropdownshown = false;
            return;
        }

        var html = '<div id="dropdown" class="drop" data-item-type="'
            +itemType+'" data-item-dir="'+src_dir+'">';
        var dropDownEl;

        html += '<label for="data-src-dir">目标文件夹</label>';
        html += '</br>';
        html += '<input id="data-src-dir" type="text" />';
        html += '<img id="dir-toggle" class="svg" src="/core/img/actions/caret.png"/>';

        html += '<br/>';
        html += '<textarea style="display: none" id="dir-selecter"></textarea>';
        html += '<button type="submit">共享至资料库</button>';

        html += '</div>';

        dropDownEl = $(html);
        dropDownEl.appendTo(appendTo);

        $('#dir-toggle').click(function(){
            $('#dir-selecter').toggle();
        })

        this.dropdownshown = true;
    }
}


$(document).ready(function (){
    var disablePublic = $('#disablePublic').data('status');
    //not show yet,why?
    console.log('disablePublic');
    console.log(disablePublic);
    if (typeof FileActions !== 'undefined'
        && !disablePublic) {
        FileActions.register('all',
            'public',
            OC.PERMISSION_READ,
            OC.imagePath('core', 'actions/share'),
            function(filename) {
                if ($('#dir').val() == '/') {
                    var src_dir = $('#dir').val() + filename;
                } else {
                    var src_dir = $('#dir').val() + '/' + filename;
                }
                var tr = FileList.findFileEl(filename);
                if ($(tr).data('type') == 'dir') {
                    var itemType = 'folder';
                } else {
                    var itemType = 'file';
                }
                var appendTo = $(tr).find('td.filename');

                PublicShare.showDropDown(itemType, appendTo, src_dir);
            }
        )
    }
});