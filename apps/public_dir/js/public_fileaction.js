/**
 * Created by wangjunlong on 14-5-9.
 */
$(document).ready(function() {

    console.log(FileActions.actions['all']['Download']);
    FileActions.register('all', 'Download',OC.PERMISSION_READ
        , function() {
            return OC.imagePath('core', 'actions/download');
        },function(filename) {
            alert(filename);
        }
    );
    console.log(FileActions.actions['all']['Download']);
})
