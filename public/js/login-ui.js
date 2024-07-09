/* Since this plugin will often be used in the https://github.com/italia/design-scuole-wordpress-theme theme, which includes a custom login form,
 this script places the SPID and CIE buttons near that login form. */

jQuery(document).on("ready", function () {
    let eid_wrapper = jQuery(".R1EIDG-wrapper");
    
    let italia_login_form = jQuery(".access-login-form"); // login form of the https://github.com/italia/design-scuole-wordpress-theme theme
    let wp_login_form = jQuery("#loginform");

    if(italia_login_form.length)
        italia_login_form.before(eid_wrapper);
    else if(wp_login_form.length){
        wp_login_form.append(eid_wrapper);
    }

    eid_wrapper.show();
})
function r1eidg_load_login_buttons() {
}

r1eidg_load_login_buttons();
