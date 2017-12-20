var editor = new MediumEditor('#^fieldname', {
    placeholder: {
        text: '^placeholder',
        hydOnClick: true
    },
    paste: {
        forcePlainText: true,
    },
    spellcheck: false,
    toolbar: false,
    autoLink: false
});
$(function() {
    $('#^fieldname').mediumInsert({
        editor: editor,
        addons: {
            images: false,
            images2: {
                preview: false,
                captions: false,
                styles: false,
                deleteScript: null,
                fileUploadOptions: {
                    url: '^site_urlmedium-editor-upload',
                    acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
                    maxFileSize: ^max_image_filesize
                },
                messages: {
                    acceptFileTypesError: '^image_type_error',
                    maxFileSizeError: '^image_size_error',
                    mdlThemeDialog: ^is_mdl,
                    acceptFileTypesErrorTitle: '^image_type_error_title',
                    maxFileSizeErrorTitle: '^image_size_error_title',
                    uploadErrorTitle: '^image_upload_error_title',
                }
            },
            videos: {
                actions: {
                    remove: {
                        label: '<span class="fa fa-times"></span>',
                        clicked: function () {
                            var $event = $.Event('keydown');

                            $event.which = 8;
                            $(document).trigger($event);
                        }
                    }
                },
            },
            embeds: false,
            embeds2: {
                styles: null,
                placeholder: '^embed_placeholder',
            },
        },
    });
});
