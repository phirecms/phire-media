/**
 * Media Module Scripts for Phire CMS 2
 */

phire.mediaActionCount = 1;

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

phire.setDefaultAllowedTypes = function() {
    jax('#allowed_types').val('ai,aif,aiff,avi,bmp,bz2,csv,doc,docx,eps,fla,flv,gif,gz,jpe,jpg,jpeg,log,md,mov,mp2,mp3,mp4,mpg,mpeg,otf,pdf,png,ppt,pptx,psd,rar,svg,swf,tar,tbz,tbz2,tgz,tif,tiff,tsv,ttf,txt,wav,wma,wmv,xls,xlsx,xml,zip');
};

phire.setDefaultDisallowedTypes = function() {
    jax('#disallowed_types').val('css,htm,html,js,json,pgsql,php,php3,php4,php5,sql,sqlite,yaml,yml');
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
            var json = jax.get(phireCookie.app_uri + '/media/libraries/json/' + jax('#id').val());
            if (json.length > 0) {
                phire.addMediaActions(json);
            }
        }
    }
});