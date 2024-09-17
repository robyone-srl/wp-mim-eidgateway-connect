/* This script places the buttons in the right place of the login form. */

jQuery(document).on("ready", function () {
    let eid_wrapper = jQuery(".R1EIDG-wrapper.from-R1EIDG");   // Searches for the login buttons drawn by the plugin.
                                                                // The class "from-R1EIDG" specifies that the buttons were not drawn from a shortcode, but by the plugin itself.

    let italia_login_form = jQuery(".access-login-form"); // login form of the https://github.com/italia/design-scuole-wordpress-theme theme
    let wp_login_form = jQuery("#loginform"); // WordPress login form

    if (italia_login_form.length) {
        italia_login_form.before(eid_wrapper);
        eid_wrapper.show();
    }
    else if (wp_login_form.length) {
        wp_login_form.append(eid_wrapper);
        eid_wrapper.show();
    }
    else {
        eid_wrapper.remove();
    }

    if(eid_wrapper.data("hide-login"))
        italia_login_form.remove();
})
function r1eidg_load_login_buttons() {
}

r1eidg_load_login_buttons();
