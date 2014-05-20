/**
 * Created by kevin on 20/5/14.
 * public_dir app main js file
 */

$(document).ready(function() {
    //show list
    $('#new').click(function(event) {
        event.stopPropagation();
    });
    $('#new a').click(function() {
        $('#new>ul').toggle();
        $('#new').toggleClass('active');
    });

    //create new folder
    $('#new li').click(function() {
        if ($('#new_fold').length == 0) {
            $(this).append('<div id="new_fold">' +
                '<input type="text" value="新文件夹"/>' +
                '<input type="button" value="建立" onclick="new_fold()"/>' +
                '</div>')
        } else {
            $('#new_fold').remove();
        }
    });
});

function new_fold()
{
    var fold_name = $('#new_fold').find('input[type=text]').val();
    var dir = $('#dir').val();

    //todo:check if the dir name already exist here
    $.post(
        OC.filePath('public_dir','ajax','newfolder.php'),
        {dir:dir, foldername:fold_name},
        function(result) {
            if (result.status === 'success') {
                var date=new Date();
                FileList.addDir(fold_name, 0, date, hidden);
                var tr = FileList.findFileEl(fold_name);
                tr.attr('data-id', result.data.id);
            } else {
                OC.dialogs.alert(result.data.message, t('core', 'Could not create folder'));
            }
        }
    );
}
