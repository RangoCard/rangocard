$.fn.editable.defaults.mode = 'inline';
$.fn.editableform.loading = "<div class='editableform-loading'><i class='ace-icon fa fa-spinner fa-spin fa-2x light-blue'></i></div>";
$.fn.editableform.buttons = '<button type="submit" class="btn btn-info editable-submit"><i class="ace-icon fa fa-check"></i></button>' +
    '<button type="button" class="btn editable-cancel"><i class="ace-icon fa fa-times"></i></button>';


try {//ie8 throws some harmless exceptions, so let's catch'em

    //first let's add a fake appendChild method for Image element for browsers that have a problem with this
    //because editable plugin calls appendChild, and it causes errors on IE
    try {
        document.createElement('IMG').appendChild(document.createElement('B'));
    } catch (e) {
        Image.prototype.appendChild = function (el) {
        }
    }

    var last_gritter;
    $('#fileSrc').editable({
        type: 'image',
        name: 'fileSrc',
        value: null,
        onblur: 'ignore',
        image: {
            //specify ace file input plugin's options here
            btn_choose: 'Alterar Foto',
            droppable: true,
            maxSize: 5242880,//~100Kb

            //and a few extra ones here
            name: 'fileSrc',//put the field name here as well, will be used inside the custom plugin
            on_error: function (error_type) {//on_error function will be called when the selected file has a problem
                if (last_gritter) $.gritter.remove(last_gritter);
                if (error_type == 1) {//file format error
                    last_gritter = $.gritter.add({
                        title: 'File is not an image!',
                        text: 'Please choose a jpg|gif|png image!',
                        class_name: 'gritter-error gritter-center'
                    });
                } else if (error_type == 2) {//file size rror
                    last_gritter = $.gritter.add({
                        title: 'File too big!',
                        text: 'Image size should not exceed 5MB!',
                        class_name: 'gritter-error gritter-center'
                    });
                }
                else {//other error
                }
            },
            on_success: function () {
                $.gritter.removeAll();
            }
        },
        url: function (params) {
            // ***UPDATE AVATAR HERE*** //
            var submit_url = fileUploadUrl;//please modify submit_url accordingly
            var deferred = null;
            var fileSrc = '#fileSrc';

            //if value is empty (""), it means no valid files were selected
            //but it may still be submitted by x-editable plugin
            //because "" (empty string) is different from previous non-empty value whatever it was
            //so we return just here to prevent problems
            var value = $(fileSrc).next().find('input[type=hidden]:eq(0)').val();
            if (!value || value.length == 0) {
                deferred = new $.Deferred;
                deferred.resolve();
                return deferred.promise();
            }

            var $form = $(fileSrc).next().find('.editableform:eq(0)');
            var pictureSrc = $('.picture-src').val();//primary key to be sent to server

            var ie_timeout = null;

            if ("FormData" in window) {
                var formData_object = new FormData();//create empty FormData object

                //serialize our form (which excludes file inputs)
                $.each($form.serializeArray(), function (i, item) {
                    //add them one by one to our FormData
                    formData_object.append(item.name, item.value);
                });
                //and then add files
                $form.find('input[type=file]').each(function () {
                    var field_name = $(this).attr('name');
                    var files = $(this).data('ace_input_files');
                    if (files && files.length > 0) {
                        formData_object.append(field_name, files[0]);
                    }
                });

                //append primary key to our formData
                formData_object.append('pictureSrc', pictureSrc);

                deferred = $.ajax({
                    url: submit_url,
                    type: 'POST',
                    processData: false,//important
                    contentType: false,//important
                    dataType: 'json',//server response type
                    data: formData_object
                })
            }
            else {
                deferred = new $.Deferred;

                var temporary_iframe_id = 'temporary-iframe-' + (new Date()).getTime() + '-' + (parseInt(Math.random() * 1000));
                var temp_iframe =
                    $('<iframe id="' + temporary_iframe_id + '" name="' + temporary_iframe_id + '" \
                                    frameborder="0" width="0" height="0" src="about:blank"\
                                    style="position:absolute; z-index:-1; visibility: hidden;"></iframe>')
                        .insertAfter($form);

                $form.append('<input type="hidden" name="temporary-iframe-id" value="' + temporary_iframe_id + '" />');

                //append primary key (path) to our form
                $('<input type="hidden" name="pictureSrc"/>').val(pictureSrc).appendTo($form);

                temp_iframe.data('deferrer', deferred);
                //we save the deferred object to the iframe and in our server side response
                //we use "temporary-iframe-id" to access iframe and its deferred object

                $form.attr({
                    action: submit_url,
                    method: 'POST',
                    enctype: 'multipart/form-data',
                    target: temporary_iframe_id //important
                });

                $form.get(0).submit();

                //if we don't receive any response after 30 seconds, declare it as failed!
                ie_timeout = setTimeout(function () {
                    ie_timeout = null;
                    temp_iframe.attr('src', 'about:blank').remove();
                    deferred.reject({'status': 'fail', 'message': 'Timeout!'});
                }, 30000);
            }


            //deferred callbacks, triggered by both ajax and iframe solution
            deferred
                .done(function (result) {//success
                    if (typeof result.fileName !== 'undefined') {
                        $(fileSrc).get(0).src = result.url;
                        $('.picture-src').val(result.url);
                        $(fileSrc).editable('destroy');
                        $(fileSrc).show();
                    } else {
                        alert("There was an error");
                    }
                })
                .fail(function (result) {//failure
                    alert("There was an error");
                })
                .always(function () {//called on both success and failure
                    if (ie_timeout) clearTimeout(ie_timeout);
                    ie_timeout = null;
                });

            return deferred.promise();
            // ***END OF UPDATE AVATAR HERE*** //
        },

        success: function (response, newValue) {
        }
    });
} catch (e) {
}

if (location.protocol == 'file:') alert("For uploading to server, you should access this page using 'http' protocal, i.e. via a webserver.");