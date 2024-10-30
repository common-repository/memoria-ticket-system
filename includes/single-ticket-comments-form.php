<?php
 
// Do not delete these lines
 if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
 die ('Please do not load this page directly. Thanks!');
 
 if ( post_password_required() ) { ?>
 <p>This post is password protected. Enter the password to view comments.</p>
 <?php
 return;
 }
?>
 
<!-- You can start editing here. -->
<div id="comment">

 
<?php if ( true || 'open' == $post->comment_status) : //always show comment form?>
 
<div id="respond">
  
<div>
 <small><?php cancel_comment_reply_link(); ?></small>
</div>
 
<?php if ( false && (get_option('comment_registration') && !$user_ID) ) : //Not required to be logged in ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to post a comment.</p>
<?php else : ?>
<h2>New reply</h2>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
 
<?php if ( $user_ID ) : ?>
 
<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="Log out of this account">Log out &raquo;</a></p>
 
<?php else : ?>
 

<?php endif; ?>
 
<!--<p><small><strong>XHTML:</strong> You can use these tags: <code><?php echo allowed_tags(); ?></code></small></p>-->
 
<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>
<div style="float:right;position:relative"> 
<input name="submit" type="submit" id="submit" tabindex="5" value="<?php echo __("Submit"); ?>" />
</div>
<div style="clear:both"></div>
<?php comment_id_fields(); ?>

<?php do_action('comment_form', $post->ID); ?>
 
</form>
 
<?php endif; // If registration required and not logged in ?>
</div>
 
<?php endif;  ?>
</div>