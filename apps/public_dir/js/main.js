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
    $('#new li p').click(function() {
        if ($('#new_fold').length == 0) {
            $(this).parent().append('<div id="new_fold">' +
                '<input type="text" value="新文件夹" placeholder="新文件夹"/>' +
                '<input type="button" value="建立"/>' +
                '</div>')
            $('#new_fold input[type=button]').click(new_fold);
        } else {
            $('#new_fold input[type=button]').unbind('click');
            $('#new_fold').remove();
        }
    });

    if ( $('#file_upload_start').exists()&& $('#file_upload_start').is(':visible') ) {

        var file_upload_param = {
            dropZone: $('#content'), // restrict dropZone to content div
            autoUpload: false,
            sequentialUploads: true,
            //singleFileUploads is on by default, so the data.files array will always have length 1
            /**
             * on first add of every selection
             * - check all files of originalFiles array with files in dir
             * - on conflict show dialog
             *   - skip all -> remember as single skip action for all conflicting files
             *   - replace all -> remember as single replace action for all conflicting files
             *   - choose -> show choose dialog
             *     - mark files to keep
             *       - when only existing -> remember as single skip action
             *       - when only new -> remember as single replace action
             *       - when both -> remember as single autorename action
             * - start uploading selection
             * @param {object} e
             * @param {object} data
             * @returns {boolean}
             */
            add: function(e, data) {
                OC.Upload.log('add', e, data);
                var that = $(this);

                // we need to collect all data upload objects before starting the upload so we can check their existence
                // and set individual conflict actions. unfortunately there is only one variable that we can use to identify
                // the selection a data upload is part of, so we have to collect them in data.originalFiles
                // turning singleFileUploads off is not an option because we want to gracefully handle server errors like
                // already exists

                // create a container where we can store the data objects
                if ( ! data.originalFiles.selection ) {
                    // initialize selection and remember number of files to upload
                    data.originalFiles.selection = {
                        uploads: [],
                        filesToUpload: data.originalFiles.length,
                        totalBytes: 0
                    };
                }
                var selection = data.originalFiles.selection;

                // add uploads
                if ( selection.uploads.length < selection.filesToUpload ) {
                    // remember upload
                    selection.uploads.push(data);
                }

                //examine file
                var file = data.files[0];
                try {
                    // FIXME: not so elegant... need to refactor that method to return a value
                    Files.isFileNameValid(file.name, FileList.getCurrentDirectory());
                }
                catch (errorMessage) {
                    data.textStatus = 'invalidcharacters';
                    data.errorThrown = errorMessage;
                }

                if (file.type === '' && file.size === 4096) {
                    data.textStatus = 'dirorzero';
                    data.errorThrown = t('files', 'Unable to upload {filename} as it is a directory or has 0 bytes',
                        {filename: file.name}
                    );
                }

                // add size
                selection.totalBytes += file.size;

                //check max upload size
                if (selection.totalBytes > $('#max_upload').val()) {
                    data.textStatus = 'notenoughspace';
                    data.errorThrown = t('files', 'Not enough space available');
                }

                // end upload for whole selection on error
                if (data.errorThrown) {
                    // trigger fileupload fail
                    var fu = that.data('blueimp-fileupload') || that.data('fileupload');
                    fu._trigger('fail', e, data);
                    return false; //don't upload anything
                }

                // check existing files when all is collected
                if ( selection.uploads.length >= selection.filesToUpload ) {

                    //remove our selection hack:
                    delete data.originalFiles.selection;

                    var callbacks = {

                        onNoConflicts: function (selection) {
                            $.each(selection.uploads, function(i, upload) {
                                upload.submit();
                            });
                        },
                        onSkipConflicts: function (selection) {
                            //TODO mark conflicting files as toskip
                        },
                        onReplaceConflicts: function (selection) {
                            //TODO mark conflicting files as toreplace
                        },
                        onChooseConflicts: function (selection) {
                            //TODO mark conflicting files as chosen
                        },
                        onCancel: function (selection) {
                            $.each(selection.uploads, function(i, upload) {
                                upload.abort();
                            });
                        }
                    };

                    OC.Upload.checkExistingFiles(selection, callbacks);

                }

                return true; // continue adding files
            },
            /**
             * called after the first add, does NOT have the data param
             * @param {object} e
             */
            start: function(e) {
                OC.Upload.log('start', e, null);
            },
            submit: function(e, data) {
                OC.Upload.rememberUpload(data);
                if ( ! data.formData ) {
                    // noone set update parameters, we set the minimum
                    data.formData = {
                        requesttoken: oc_requesttoken,
                        dir: $('#dir').val()
                    };
                }
            },
            fail: function(e, data) {
                OC.Upload.log('fail', e, data);
                if (typeof data.textStatus !== 'undefined' && data.textStatus !== 'success' ) {
                    if (data.textStatus === 'abort') {
                        OC.Notification.show(t('files', 'Upload cancelled.'));
                    } else {
                        // HTTP connection problem
                        OC.Notification.show(data.errorThrown);
                    }
                    //hide notification after 10 sec
                    setTimeout(function() {
                        OC.Notification.hide();
                    }, 10000);
                }
                OC.Upload.deleteUpload(data);
            },
            /**
             * called for every successful upload
             * @param {object} e
             * @param {object} data
             */
            done:function(e, data) {
                OC.Upload.log('done', e, data);
                // handle different responses (json or body from iframe for ie)
                var response;
                if (typeof data.result === 'string') {
                    response = data.result;
                } else {
                    //fetch response from iframe
                    response = data.result[0].body.innerText;
                }
                var result=$.parseJSON(response);

                delete data.jqXHR;

                if (result.status === 'error' && result.data && result.data.message){
                    data.textStatus = 'servererror';
                    data.errorThrown = result.data.message;
                    var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload');
                    fu._trigger('fail', e, data);
                } else if (typeof result[0] === 'undefined') {
                    data.textStatus = 'servererror';
                    data.errorThrown = t('files', 'Could not get result from server.');
                    var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload');
                    fu._trigger('fail', e, data);
                } else if (result[0].status === 'existserror') {
                    //show "file already exists" dialog
                    var original = result[0];
                    var replacement = data.files[0];
                    var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload');
                    OC.dialogs.fileexists(data, original, replacement, OC.Upload, fu);
                } else if (result[0].status !== 'success') {
                    //delete data.jqXHR;
                    data.textStatus = 'servererror';
                    data.errorThrown = result[0].data.message; // error message has been translated on server
                    var fu = $(this).data('blueimp-fileupload') || $(this).data('fileupload');
                    fu._trigger('fail', e, data);
                }
            },
            /**
             * called after last upload
             * @param {object} e
             * @param {object} data
             */
            stop: function(e, data) {
                OC.Upload.log('stop', e, data);
            }
        };

        // initialize jquery fileupload (blueimp)
        var fileupload = $('#file_upload_start').fileupload(file_upload_param);
        window.file_upload_param = fileupload;

        if (supportAjaxUploadWithProgress()) {

            // add progress handlers
            fileupload.on('fileuploadadd', function(e, data) {
                OC.Upload.log('progress handle fileuploadadd', e, data);
                //show cancel button
                //if (data.dataType !== 'iframe') { //FIXME when is iframe used? only for ie?
                //	$('#uploadprogresswrapper input.stop').show();
                //}
            });
            // add progress handlers
            fileupload.on('fileuploadstart', function(e, data) {
                OC.Upload.log('progress handle fileuploadstart', e, data);
                $('#uploadprogresswrapper input.stop').show();
                $('#uploadprogressbar').progressbar({value:0});
                $('#uploadprogressbar').fadeIn();
            });
            fileupload.on('fileuploadprogress', function(e, data) {
                OC.Upload.log('progress handle fileuploadprogress', e, data);
                //TODO progressbar in row
            });
            fileupload.on('fileuploadprogressall', function(e, data) {
                OC.Upload.log('progress handle fileuploadprogressall', e, data);
                var progress = (data.loaded / data.total) * 100;
                $('#uploadprogressbar').progressbar('value', progress);
            });
            fileupload.on('fileuploadstop', function(e, data) {
                OC.Upload.log('progress handle fileuploadstop', e, data);

                $('#uploadprogresswrapper input.stop').fadeOut();
                $('#uploadprogressbar').fadeOut();
                Files.updateStorageStatistics();
            });
            fileupload.on('fileuploadfail', function(e, data) {
                OC.Upload.log('progress handle fileuploadfail', e, data);
                //if user pressed cancel hide upload progress bar and cancel button
                if (data.errorThrown === 'abort') {
                    $('#uploadprogresswrapper input.stop').fadeOut();
                    $('#uploadprogressbar').fadeOut();
                }
            });

        } else {
            console.log('skipping file progress because your browser is broken');
        }
    }
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
                FileList.addDir(fold_name, 0, date, false);
                var tr = FileList.findFileEl(fold_name);
                tr.attr('data-id', result.data.id);
            } else {
                OC.dialogs.alert(result.data.message, t('core', 'Could not create folder'));
            }
        }
    );
}

function upload_file() {

}

/**
 * keeps track of uploads in progress and implements callbacks for the conflicts dialog
 * @type {OC.Upload}
 */
OC.Upload = {
    _uploads: [],
    /**
     * deletes the jqHXR object from a data selection
     * @param {object} data
     */
    deleteUpload:function(data) {
        delete data.jqXHR;
    },
    /**
     * cancels all uploads
     */
    cancelUploads:function() {
        this.log('canceling uploads');
        jQuery.each(this._uploads,function(i, jqXHR) {
            jqXHR.abort();
        });
        this._uploads = [];
    },
    rememberUpload:function(jqXHR) {
        if (jqXHR) {
            this._uploads.push(jqXHR);
        }
    },
    /**
     * Checks the currently known uploads.
     * returns true if any hxr has the state 'pending'
     * @returns {boolean}
     */
    isProcessing:function() {
        var count = 0;

        jQuery.each(this._uploads,function(i, data) {
            if (data.state() === 'pending') {
                count++;
            }
        });
        return count > 0;
    },
    /**
     * callback for the conflicts dialog
     * @param {object} data
     */
    onCancel:function(data) {
        this.cancelUploads();
    },
    /**
     * callback for the conflicts dialog
     * calls onSkip, onReplace or onAutorename for each conflict
     * @param {object} conflicts - list of conflict elements
     */
    onContinue:function(conflicts) {
        var self = this;
        //iterate over all conflicts
        jQuery.each(conflicts, function (i, conflict) {
            conflict = $(conflict);
            var keepOriginal = conflict.find('.original input[type="checkbox"]:checked').length === 1;
            var keepReplacement = conflict.find('.replacement input[type="checkbox"]:checked').length === 1;
            if (keepOriginal && keepReplacement) {
                // when both selected -> autorename
                self.onAutorename(conflict.data('data'));
            } else if (keepReplacement) {
                // when only replacement selected -> overwrite
                self.onReplace(conflict.data('data'));
            } else {
                // when only original seleted -> skip
                // when none selected -> skip
                self.onSkip(conflict.data('data'));
            }
        });
    },
    /**
     * handle skipping an upload
     * @param {object} data
     */
    onSkip:function(data) {
        this.log('skip', null, data);
        this.deleteUpload(data);
    },
    /**
     * handle replacing a file on the server with an uploaded file
     * @param {object} data
     */
    onReplace:function(data) {
        this.log('replace', null, data);
        if (data.data) {
            data.data.append('resolution', 'replace');
        } else {
            data.formData.push({name:'resolution', value:'replace'}); //hack for ie8
        }
        data.submit();
    },
    /**
     * handle uploading a file and letting the server decide a new name
     * @param {object} data
     */
    onAutorename:function(data) {
        this.log('autorename', null, data);
        if (data.data) {
            data.data.append('resolution', 'autorename');
        } else {
            data.formData.push({name:'resolution', value:'autorename'}); //hack for ie8
        }
        data.submit();
    },
    _trace:true, //TODO implement log handler for JS per class?
    log:function(caption, e, data) {
        if (this._trace) {
            console.log(caption);
            console.log(data);
        }
    },
    /**
     * TODO checks the list of existing files prior to uploading and shows a simple dialog to choose
     * skip all, replace all or choose which files to keep
     * @param {array} selection of files to upload
     * @param {object} callbacks - object with several callback methods
     * @param {function} callbacks.onNoConflicts
     * @param {function} callbacks.onSkipConflicts
     * @param {function} callbacks.onReplaceConflicts
     * @param {function} callbacks.onChooseConflicts
     * @param {function} callbacks.onCancel
     */
    checkExistingFiles: function (selection, callbacks) {
        // TODO check filelist before uploading and show dialog on conflicts, use callbacks
        callbacks.onNoConflicts(selection);
    }
};
