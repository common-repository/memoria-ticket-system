jQuery("document").ready(function (){

    jQuery("a#new_category").click(function(e){
        var row = "<tr>"+
                "<td><input type='text' name='sts_categories[]'/></td>"+
                "<td><a href='#' class='btn_remove'>Remove</a></td>"+
            "</tr>";
        jQuery("tr#btn_new_category").before(row);
        hookRemoveToBtn();
        return false;
    });

    jQuery("a#new_department").click(function(e){
        var row = "<tr>"+
                "<td><input type='text' name='sts_departments[]'/></td>"+
                "<td><a href='#' class='btn_remove'>Remove</a></td>"+
            "</tr>";
        jQuery("tr#btn_new_department").before(row);
        hookRemoveToBtn();
        return false;
    });
    jQuery("#recaptcha_checkbox").change(function(){
        hookreCaptchaCheckbox();
    });
    hookRemoveToBtn();
    hookreCaptchaCheckbox();

    
});

jQuery(function($) {
    $( "#sts-ticket-tabs" ).tabs();
});
function hookRemoveToBtn(){
    jQuery("a.btn_remove").click(function(){
        jQuery(this).closest("tr").remove();
        return false;
    });
}
function load_default(to, from){
    var r = confirm("Do you really want to load the default content?");
    jQuery("#email_notification-html").trigger("click");
    if (r == true) {
        jQuery("#"+to).val(jQuery("#"+from).val());
    } else {
        return false;
    }
    return false; 
}
function hookreCaptchaCheckbox(){
    if(jQuery("#recaptcha_checkbox").is(':checked')){
        jQuery("#recaptcha_table").find("input[type='password'], input[type='text']").attr("disabled", false);
    } else {
        jQuery("#recaptcha_table").find("input[type='password'], input[type='text']").attr("disabled", true);
    }
}