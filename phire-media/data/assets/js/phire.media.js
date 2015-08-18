/**
 * Media Module Scripts for Phire CMS 2
 */

phire.mediaActionCount = 1;
phire.batchCount = 1;

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
});
