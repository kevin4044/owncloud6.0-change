/**
 * Created by wangjunlong on 14-5-7.
 */

var PublicShare = {
    showDropDown:function (itemType, appendTo) {

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

            }
        )
    }
});