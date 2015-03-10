/**
 * Media Module Scripts for Phire CMS 2
 */

phire.mediaActionCount = 1;

phire.addMediaActions = function() {

    // Add action name field
    jax('#action_name_1').clone({
        "name" : 'action_name_' + phire.validatorCount,
        "id"   : 'action_name_' + phire.validatorCount
    }).appendTo(jax('#action_name_1').parent());

    // Add action method field
    jax('#action_method_1').clone({
        "name" : 'action_method_' + phire.validatorCount,
        "id"   : 'action_method_' + phire.validatorCount
    }).appendTo(jax('#action_method_1').parent());

    // Add action params field
    jax('#action_params_1').clone({
        "name" : 'action_params_' + phire.validatorCount,
        "id"   : 'action_params_' + phire.validatorCount
    }).appendTo(jax('#action_params_1').parent());

    // Add action params field
    jax('#action_quality_1').clone({
        "name" : 'action_quality_' + phire.validatorCount,
        "id"   : 'action_quality_' + phire.validatorCount
    }).appendTo(jax('#action_quality_1').parent());

    return false;
};

phire.checkAllAllowedTypes = function() {
    jax('#media-library-form').checkAll('allowed_types');
};

phire.uncheckAllAllowedTypes = function() {
    jax('#media-library-form').uncheckAll('allowed_types');
};

phire.invertAllowedTypes = function() {
    jax('#media-library-form').checkInverse('allowed_types');
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
});