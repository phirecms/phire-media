/**
 * Media Module Scripts for Phire CMS 2
 */

phire.mediaActionCount = 1;
phire.batchCount       = 1;

phire.addMediaActions = function(vals) {
    if (vals == null) {
        vals = [{
            "name"    : "",
            "method"  : "----",
            "params"  : "",
            "quality" : ""
        }];
    }

    for (var i = 0; i < vals.length; i++) {
        phire.mediaActionCount++;

        // Add action name field
        jax('#action_name_1').clone({
            "name"  : 'action_name_' + phire.mediaActionCount,
            "id"    : 'action_name_' + phire.mediaActionCount,
            "value" : vals[i].name
        }).appendTo(jax('#action_name_1').parent());

        // Add action method field
        jax('#action_method_1').clone({
            "name": 'action_method_' + phire.mediaActionCount,
            "id": 'action_method_' + phire.mediaActionCount
        }).appendTo(jax('#action_method_1').parent());

        jax('#action_method_' + phire.mediaActionCount).val(vals[i].method);

        // Add action params field
        jax('#action_params_1').clone({
            "name"  : 'action_params_' + phire.mediaActionCount,
            "id"    : 'action_params_' + phire.mediaActionCount,
            "value" : vals[i].params
        }).appendTo(jax('#action_params_1').parent());

        // Add action params field
        jax('#action_quality_1').clone({
            "name"  : 'action_quality_' + phire.mediaActionCount,
            "id"    : 'action_quality_' + phire.mediaActionCount,
            "value" : vals[i].quality
        }).appendTo(jax('#action_quality_1').parent());
    }

    return false;
};

phire.addBatchFile = function(max) {
    if (phire.batchCount < max) {
        phire.batchCount++;

        // Add batch file field
        jax('#file_1').clone({
            "name": 'file_' + phire.batchCount,
            "id": 'file_' + phire.batchCount
        }).appendTo(jax('#file_1').parent());
    } else {
        alert('The max number of files that can be uploaded at once is ' + max + '.');
    }

    return false;
};

phire.setDefaultAllowedTypes = function() {
    jax('#allowed_types').val('ai,aif,aiff,avi,bmp,bz2,csv,doc,docx,eps,fla,flv,gif,gz,jpe,jpg,jpeg,log,md,mov,mp2,mp3,mp4,mpg,mpeg,otf,pdf,png,ppt,pptx,psd,rar,svg,swf,tar,tbz,tbz2,tgz,tif,tiff,tsv,ttf,txt,wav,wma,wmv,xls,xlsx,xml,zip');
};

phire.setDefaultDisallowedTypes = function() {
    jax('#disallowed_types').val('css,htm,html,js,json,pgsql,php,php3,php4,php5,sql,sqlite,yaml,yml');
};

phire.loadEditor = function(editor, id) {
    if (null != id) {
        var w = Math.round(jax('#field_' + id).width());
        var h = Math.round(jax('#field_' + id).height());
        phire.editorIds = [{ "id" : id, "width" : w, "height" : h }];
    }

    var sysPath = '';
    if (jax.cookie.load('phire') != '') {
        var phireCookie = jax.cookie.load('phire');
        sysPath = phireCookie.base_path + phireCookie.app_uri;
    }

    if (phire.editorIds.length > 0) {
        for (var i = 0; i < phire.editorIds.length; i++) {
            if (editor == 'ckeditor') {
                if (CKEDITOR.instances['field_' + phire.editorIds[i].id] == undefined) {
                    CKEDITOR.replace(
                        'field_' + phire.editorIds[i].id,
                        {
                            width                         : 'auto',
                            height                        : phire.editorIds[i].height,
                            allowedContent                : true,
                            filebrowserBrowseUrl          : sysPath + '/media/browser?editor=ckeditor&type=file',
                            filebrowserImageBrowseUrl     : sysPath + '/media/browser?editor=ckeditor&type=image',
                            filebrowserImageBrowseLinkUrl : sysPath + '/media/browser?editor=ckeditor&type=file',
                            filebrowserWindowWidth        : '960',
                            filebrowserWindowHeight       : '720'
                        }
                    );
                }
                var eid = phire.editorIds[i].id;
                jax('#field_' + eid).keyup(function(){
                    console.log(jax('#field_' + eid).val());
                    CKEDITOR.instances['field_' + eid].setData(jax('#field_' + eid).val());
                });
            } else if (editor == 'tinymce') {
                if (tinymce.editors['field_' + phire.editorIds[i].id] == undefined) {
                    tinymce.init(
                        {
                            selector              : "textarea#field_" + phire.editorIds[i].id,
                            theme                 : "modern",
                            plugins: [
                                "advlist autolink lists link image hr", "searchreplace wordcount code fullscreen",
                                "table", "template paste textcolor"
                            ],
                            image_advtab          : true,
                            toolbar1              : "insertfile undo redo | styleselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | link image",
                            width                 : 'auto',
                            height                : phire.editorIds[i].height,
                            relative_urls         : false,
                            convert_urls          : 0,
                            remove_script_host    : 0,
                            file_browser_callback : function(field_name, url, type, win) {
                                tinymce.activeEditor.windowManager.open({
                                    title  : "Media Browser",
                                    url    : sysPath + '/media/browser?editor=tinymce&type=' + type,
                                    width  : 960,
                                    height : 720
                                }, {
                                    oninsert : function(url) {
                                        win.document.getElementById(field_name).value = url;
                                    }
                                });
                            }
                        }
                    );
                } else {
                    tinymce.get('field_' + phire.editorIds[i].id).show();
                }
                var eid = phire.editorIds[i].id;
                jax('#field_' + eid).keyup(function(){
                    tinymce.editors['field_' + eid].setContent(jax('#field_' + eid).val());
                });
            }
        }
    }
};

jax(document).ready(function(){
    if (jax('#media-form')[0] != undefined) {
        jax('#media-form').submit(function(){
            jax('#loading').show();
        });
    }
    if (jax('#media-batch-form')[0] != undefined) {
        jax('#media-batch-form').submit(function(){
            jax('#loading').show();
        });
    }
    if (jax('#medias-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#medias-form').checkAll(this.value);
            } else {
                jax('#medias-form').uncheckAll(this.value);
            }
        });
        jax('#medias-form').submit(function(){
            return jax('#medias-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#media-libraries-form')[0] != undefined) {
        jax('#checkall').click(function(){
            if (this.checked) {
                jax('#media-libraries-form').checkAll(this.value);
            } else {
                jax('#media-libraries-form').uncheckAll(this.value);
            }
        });
        jax('#media-libraries-form').submit(function(){
            return jax('#media-libraries-form').checkValidate('checkbox', true);
        });
    }
    if (jax('#media-library-form')[0] != undefined) {
        if ((jax.cookie.load('phire') != '') && (jax('#id').val() != null)) {
            var phireCookie = jax.cookie.load('phire');
            var json = jax.get(phireCookie.base_path + phireCookie.app_uri + '/media/libraries/json/' + jax('#id').val());
            if (json.length > 0) {
                phire.addMediaActions(json);
            }
        }
    }
    if (jax('#drop-zone')[0] != undefined) {
        jax('#drop-zone').on('dragover', function(e){
            jax('#drop-zone').attrib('class', 'drop-zone-on');
            e.stopPropagation();
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }, false);

        jax('#drop-zone').on('dragleave', function(e){
            jax('#drop-zone').attrib('class', 'drop-zone-off');
            e.stopPropagation();
            e.preventDefault();
        }, false);

        jax('#drop-zone').on('drop', function(e){
            jax('#drop-zone').attrib('class', 'drop-zone-off');
            e.stopPropagation();
            e.preventDefault();

            var files       = null;
            var appPath     = null;
            var contentPath = null;
            var lid         = jax('#drop-zone').data('lid');
            if ((e.dataTransfer.files !== undefined) && (e.dataTransfer.files !== null)) {
                files = e.dataTransfer.files;
            } else if ((e.target.files !== undefined) && (e.target.files !== null)) {
                files = e.target.files;
            }

            if (jax.cookie.load('phire') != '') {
                phireCookie = jax.cookie.load('phire');
                appPath     = phireCookie.base_path + phireCookie.app_uri;
                contentPath = phireCookie.base_path + phireCookie.content_path;
            }

            if ((files != null) && (appPath != null) && (contentPath != null) && (lid != null)) {
                jax('#drop-result').val('');
                for (var i = 0; i < files.length; i++) {
                    var cur    = (i + 1);
                    var form   = new FormData();
                    var result = jax('#drop-result').val() + '<div id="file-upload-' + cur + '">' +
                        files[i].name + ' <img id="file-upload-image-' + cur + '" src="' + contentPath + '/assets/phire-media/img/uploading.gif" /></div>';

                    form.append("file_" + cur, files[i]);
                    jax('#drop-result').val(result);

                    $.post(appPath + '/media/ajax/' + lid, {data : form, async : true, status : { "200" : function(response) {
                        var update = ' <strong class="upload-success">uploaded.</strong>';
                        if ((response.text != undefined) && (response.text != '')) {
                            var resp = window.jax.parseResponse(response);
                            if (resp.error != undefined) {
                                update = ' <strong class="upload-error">' + resp.error + '</strong>';
                            }
                            jax('#file-upload-image-' + resp.id).remove();
                            jax('#file-upload-' + resp.id).val(jax('#file-upload-' + resp.id).val() + update);
                        }
                    }}});
                }
            } else {
                jax('#drop-result').val(
                    '<strong class="error">Drag and drop files are not supported in this browser.</strong> ' +
                    'Try the <a class="normal-link" href="' + window.location.href + '?basic=1">basic batch uploader</a>.'
                );
            }
        });
    }
});
