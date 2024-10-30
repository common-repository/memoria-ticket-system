<div class="wrap">
<?php echo sts_get_creator_header(); ?>
<h3><a style="color:#006863;text-decoration:none" href="#" onclick="jQuery('#slideActivate').slideToggle()">> How to activate the ticket system</a></h3>
<p id="slideActivate" style="display:none">To add the ticket system on a page just include this text in the content:<br/>
	<span style="color:red;cursor:text">[sts_memoria_ticket_system]</span>
</p>
<h2>Settings - Memoria ticket system</h2>
<form method="post" action="options.php">
	<?php 
          settings_fields('sts_ticket_options'); 
        
          do_settings_sections('sts_ticket_options.php');


          $sts_op_default = get_option('sts_default_approver');
          $sts_op_powerdby = get_option('sts_powerdby_display');
    ?>
  <p><input id="powerdby_checkbox" type="checkbox" name="sts_powerdby_display" value="1" <?php checked( "1", $sts_op_powerdby, true );?> />
    <label for="powerdby_checkbox">Display <i>created by</i> on frontend</label>
  </p>
    <div id="sts-ticket-tabs">
<ul>
<li><a href="#tabs-1">Categories</a></li>
<li><a href="#tabs-2">Departments</a></li>
<!---<li><a href="#tabs-3">Approvers</a></li>-->
<li><a href="#tabs-4">Email notification</a></li>
<li><a href="#tabs-5">Style sheet</a></li>
<li><a href="#tabs-6">reCaptcha</a></li>
</ul>
<div id="tabs-1">
<table width="500px" border="0" cellspacing="0" cellpadding="3" class="form-table">                 
           <tr>
           	<td><h3>Categories</h3></td>
           </tr>
           <?php
           	$categories = sts_get_categories();
           	if(is_array($categories) && count($categories)>0){
           		foreach ($categories as $key => $value) {
           		echo '
           			<tr>
            			<td><input type="text" name="sts_categories['.$key.']" value="'.$value.'" /></td>
            			<td><a href="#" class="btn_remove">Remove</a></td>
          			</tr>
          			';
           		}
           	} else {
           		echo "<tr><td><i>No categories created</i></td></tr>";
           	}

           ?>
          <tr id="btn_new_category">
           	<td><a href="#" id="new_category">+ Add new category</a></td>
           </tr>
         </table>
</div>
<div id="tabs-2">
<table width="500px" border="0" cellspacing="0" cellpadding="3" class="form-table">                 
           <tr>
           	<td><h3>Departments</h3></td>
           </tr>
           <?php
           	$departments = sts_get_departments();
           	if(is_array($departments) && count($departments)>0){
           		foreach ($departments as $key => $value) {
           		echo '
           			<tr>
            			<td><input type="text" name="sts_departments['.$key.']" value="'.$value.'" /></td>
            			<td><a href="#" class="btn_remove">Remove</a></td>
          			</tr>
          			';
           		}
           	} else {
           		echo "<tr><td><i>No departments created</i></td></tr>";
           	}

           ?>
          <tr id="btn_new_department">
           	<td><a href="#" id="new_department">+ Add new department</a></td>
           </tr>
         </table>
</div>
<?php /*
<div id="tabs-3">
<table width="500px" border="0" cellspacing="0" cellpadding="3" class="form-table">
    
    <tr>
            <th><label for="sts_default_approver">Default approver:</label></th>
            <td> <?php wp_dropdown_users(array('name' => 'sts_default_approver', 'selected' => $sts_op_default)); ?></td>
          </tr>
    <?php
            $approvers = sts_get_approvers();
            if(is_array($approvers) && count($approvers)>0){
              foreach ($approvers as $key => $value) {
              echo '
                <tr>
                  <td><input type="text" name="sts_appprovers['.$key.']" value="'.$value.'" /></td>
                  <td><a href="#" class="btn_remove">Remove</a></td>
                </tr>
                ';
              }
            } else {
              echo "<tr><td><i>No approvers created</i></td></tr>";
            }

           ?>
          <tr id="btn_new_approver">
            <td><a href="#" id="new_approver">+ Create new approver</a></td>
           </tr> 
</table>
</div>
*/?>
<div id="tabs-4">
  <table border="0" cellspacing="0" cellpadding="3" class="form-table">
    <tr>
      <td>
        <?php
        $content = sts_get_email_notification();
        $editor_id = 'email_notification';
        $settings = array('textarea_name' => 'sts_email_notification' );
        wp_editor( $content, $editor_id, $settings );
        ?>
        <p class="description>">You can use [%name], [%email], [%category], 
  [%department], [%subject], [%message], [%ticket_id], [%ticket_url], [%blogg_name]</p>
<textarea id="defalut_email_notification" style="display:none"><?php echo sts_get_default_email_msg(); ?></textarea>
<br/>
<p><a href="#" onclick="load_default('email_notification', 'defalut_email_notification')">Load the default email notification</a></p>
      </td>
    </tr>
</table>
</div>
<div id="tabs-5">
  <table width="500px" border="0" cellspacing="0" cellpadding="3" class="form-table">
    <tr>
      <td>
        <?php
        $style_code = sts_get_plugin_style();
        ?>
        <textarea name="sts_plugin_style" id="sts_plugin_style" style="width:100%" rows="15"><?php echo $style_code; ?></textarea>
        <textarea id="defalut_plugin_style" style="display:none"><?php echo sts_get_default_style(); ?></textarea>
        <br/>
        <br/>
        <p class="description>"><a href="#" onclick="load_default('sts_plugin_style', 'defalut_plugin_style')">Load the default style</a></p>
      </td>
    </tr>
</table>
</div>
<div id="tabs-6">
  <table border="0" id="recaptcha_table" cellspacing="0" cellpadding="3" class="form-table">
    <tr>
      <td>
        <?php
        $recaptcha_settings = get_option('sts_recaptcha_settings');
        ?>
        <p>Activate reCaptcha: <input id="recaptcha_checkbox" type="checkbox" name="sts_recaptcha_settings[active]" value="1" <?php checked( "1", $recaptcha_settings['active'], true );?> </p>
        <p>public key: <input type="text" name="sts_recaptcha_settings[public_key]" value="<?php echo $recaptcha_settings['public_key']; ?>" /></p>
        <p>private key: <input type="password" name="sts_recaptcha_settings[private_key]" value="<?php echo $recaptcha_settings['private_key']; ?>" /></p>
        <br/>
        <p>To get keys go to: <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a></p>
      </td>
    </tr>
</table>
</div>
</div>
  
         <?php submit_button(); ?>
         </form> 
</div>

