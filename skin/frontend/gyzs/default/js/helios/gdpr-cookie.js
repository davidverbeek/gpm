// Cookie package set - PP 23052018
jQuery(document).ready(function(){
    jQuery(".cookie_setting").change(function(){
        var cookiePackageId = jQuery(this).val();
        // based on selection set cookie value for tracking enable or disable
        // jQuery.cookie("acceptance_cookie", cookiePackageId);
        document.cookie = "acceptance_cookie = " + cookiePackageId + ';path=/';
        localStorage.setItem("acceptance_cookie", cookiePackageId);
    });
});