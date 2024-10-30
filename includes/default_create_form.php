<?php

$recaptcha_settings = get_option('sts_recaptcha_settings');
if($recaptcha_settings['active']=="1"){
	require_once(plugin_dir_path( __FILE__ ) . '/recaptchalib.php');
	$publickey = $recaptcha_settings['public_key'];
	$privatekey = $recaptcha_settings['private_key'];
	$reError = NULL;
	$recaptcha_field = recaptcha_get_html($publickey, $reError);
}

if(is_wp_error($error) && is_array($error->get_error_codes())){
	echo '<div class="error">';
	foreach ($error->get_error_messages() as $msg) {
		echo '<p>'.$msg.'</p>';
	}
	echo '</div>';
}
$ticket_form = ""; 
if(!empty($_POST['support2']) && $_POST['support2']=="submit" && !empty( $_POST['action2'] )) {
$ticket_form .= '<font color="red"><strong>Thanks for submitting your support request!<br><strong></font>';
}
$ticket_form .= <<<HTML
<form method="post" name="sts_ticket_form" action="" id="ticket_form" enctype="multipart/form-data">
<table>
HTML;
$arr_cat = sts_get_categories();
if(is_array($arr_cat) && count($arr_cat)>0){
    $ticket_form .= "<tr><td>";
    $ticket_form .= "<label for='sts_category'>".__( "Category" )."</label>";
    $ticket_form .= "<select name='sts_category' id='sts_category'>";
    $ticket_form .= "<option value='0'>".__("Choose category")."</option>";
    foreach ($arr_cat as $key => $value) {
        $ticket_form .= "<option value='".$key."'>".$value."</option>";
    }
    $ticket_form .= "</select>";
    $ticket_form .= "</td></tr>";
}
$arr_dep = sts_get_departments();
if(is_array($arr_dep) && count($arr_dep)>0){
    $ticket_form .= "<tr><td>";
    $ticket_form .= "<label for='sts_department'>".__( "Department" )."</label>";
    $ticket_form .= "<select name='sts_department' id='sts_department'>";
    $ticket_form .= "<option value='0'>".__("Choose department")."</option>";
    foreach ($arr_dep as $key => $value) {
        $ticket_form .= "<option value='".$key."'>".$value."</option>";
    }
    $ticket_form .= "</select>";
    $ticket_form .= "</td></tr>";
}

if ( !is_user_logged_in() ) {
$ticket_form .= <<<HTML
<tr>
	<td>
		<div style="float:left">
HTML;
$ticket_form .= "<label for='sts_name'>".__("Name")."</label>";
$ticket_form .= '<input type="text" name="sts_name" id="sts_name" value="'.$_POST["sts_name"].'" />&nbsp;';
$ticket_form .= <<<HTML
		</div>
		<div style="float:left">
HTML;
$ticket_form .= "<label for='sts_email'>".__("Email")."</label>";
$ticket_form .= '<input type="text" name="sts_email" id="sts_email" value="'.$_POST["sts_email"].'" />';
$ticket_form .= <<<HTML
		</div>
		<div class="clr"></div>
	</td>
</tr>
HTML;
} //If user is not logged in
$ticket_form .= <<<HTML
<tr>
	<td>
HTML;
$ticket_form .= '<label for="sts_subject">'.__("Subject").'</label>';
$ticket_form .= '<input type="text" name="sts_subject" id="sts_subject" value="'.$_POST["sts_subject"].'" />';
$ticket_form .= <<<HTML
	</td>
</tr>
<tr>
	<td>
HTML;
$ticket_form .= '<label for="sts_message_area">'.__("Message").'</label>';
$ticket_form .= '<textarea name="sts_message" id="sts_message_area" rows="5" cols="70">'.$_POST['sts_message'].'</textarea><br />';
$ticket_form .= <<<HTML
	</td>
</tr>
<tr>
	<td align="right">
		<a href="#" onclick="jQuery('#file_attachment_wrapper').slideToggle();return false">+ Attach file</a>
		<div id="file_attachment_wrapper">
			<input type="file" name="attachment_file" id="attachment_file">
			<p>(Allowed file extensions: .jpg, .gif, .png, .zip, .pdf, .doc, .docx, .txt, .gzip, .sql, .ppt, .pptx, .rtf, .rar, .psd, .bmp, .htm, .xlsx, .xlt, .csv)</p>
		</div>
	</td>
</tr>
<tr>
	<td>&nbsp</td>
</tr>
<tr>
	<td>
		<input type="hidden" name="submit_ticket" id="submit_ticket" value="submit" />
		<input type="hidden" name="action2" value="new_ticket" />
HTML;
$ticket_form .=	'<div style="float:left">'.$recaptcha_field.'</div>
		<div style="float:right">';
$ticket_form .= '<input type="submit" value="'.__("Submit").'">';
$ticket_form .= <<<HTML
		</div>
		<div class="clr"></div>
	</td>
</tr>
HTML;
$ticket_form .= wp_nonce_field( 'new_ticket' );
$ticket_form .= <<<HTML
</table>
</form>
HTML;
if(get_option("sts_powerdby_display")=="1") : 
	$ticket_form .= '<div class="sts-ticket-footer" style="font-size:70%;text-align:right">created by <a href="http://www.memoria.se">Memoria Konsulting</a></div>';
endif;


