<?php
 /*Template Name: Single Ticket view
 	Author: Vatan Bytyqi
 */
 
get_header(); 

function sts_select_comment($id1, $id2){
	if($id1==$id2)
		return " selected";
}
?>
<div id="primary" class="content-area">
    <div id="content" class="site-content" role="main">
    <?php
    $mypost = array( 'post_type' => 'sts-ticket', );
    #$loop = new WP_Query( $mypost );
    ?>
    <?php while ( have_posts() ) : the_post();?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        	<?php
        	$post = &get_post(get_the_ID());
			$title = $post->post_title;
			$ticketID = get_post_meta( get_the_ID(), 'sts_ticket_id', true );
			$category = get_post_meta( get_the_ID(), 'sts_ticket_category', true );
			$department = get_post_meta( get_the_ID(), 'sts_ticket_department', true );
			$ticket_rate = get_post_meta( get_the_ID(), 'sts_ticket_rate', true );
        	if(is_user_logged_in()||$_GET['sts-ticket-id']==$ticketID){
        	$statuses = sts_get_statuses();
        	$select_status = '<select name="sts_status" id="sts_status">';
        	$selected_option = strtoupper(trim(get_post_meta( get_the_ID(), 'sts_ticket_status', true )));
        	foreach ($statuses as $status) {
        		$select_status .= '<option value="'.$status.'" '.selected($selected_option, strtoupper(trim($status)), false ).'>'.$status.'</a>';
        	}
        	$select_status .= "</select>";
        	if($selected_option=="CLOSED") {
        		$select_status = "<span class='status_closed'>".$statuses["st_closed"]."</span>";
        	}
			?>
			<header class="entry-header">
				<h4 class='title'>Ticket</h4>
				<?php echo "<h5 class='ticket-id'>#".get_the_ID()."</h5>"; ?>
				<br/>
				<!-- Display yellow stars based on rating -->
				<div class="stats" id="comment-o98dnsn0976q327">
					<form action="" id="sts_update_status_form" method="post">
						<?php wp_nonce_field('sts_update_status_action'); ?>
	                	<p><span class="icon-status"></span><?php echo __("Status");?> <span class="value"><?php echo $select_status; ?></span> 
	                		<input type="hidden" value="<?php echo $post->ID;?>" name="post_id"/>
	                		<input type="hidden" value="<?php echo $ticketID;?>" name="ticket_id"/>
	                		<?php if($selected_option!="CLOSED") : ?>
	                			<input type="submit" value="Save" style="display:none"/>
	                		<?php else : ?>
	                			<input type="hidden" value="Open" name="status"/>
	                			<input type="submit" value="re-open ticket"/>
	                		<?php endif; ?>
	                		</p>
	                	<?php if(!empty($category)):?>
	                		<p><span class="icon-cat"></span><?php echo __("Category");?> <span class="value"><?php echo $category ?></span></p>
	                	<?php endif; ?>
	                	<?php if(!empty($department)):?>
	                		<p><span class="icon-dep"></span><?php echo __("Department");?> <span class="value"><?php echo $department ?></span></p>
	                	<?php endif; ?>
	                	<p><span class="icon-time"></span><?php echo __("Created");?> <span class="value"><?php echo get_the_date(); echo " ".get_the_time(); ?></span></p>
	                	<?php if($selected_option=="CLOSED") : 
	                			echo '<br/><div class="smillies" style="float:left">
	                				Overall rating:  &nbsp;
									<a href="#" onclick="sts_rate_comment(\'o98dnsn0976q327\', \''.$post->ID.'\', \''.$ticketID.'\', \'1\', this); return false" title="It was a great experience" class="greenSmiley'.sts_select_comment($ticket_rate, "1").'"></a>
									<a href="#" onclick="sts_rate_comment(\'o98dnsn0976q327\', \''.$post->ID.'\', \''.$ticketID.'\', \'0\', this); return false" title="It was an OK experience" class="orangeSmiley'.sts_select_comment($ticket_rate, "0").'"></a>
									<a href="#" onclick="sts_rate_comment(\'o98dnsn0976q327\', \''.$post->ID.'\', \''.$ticketID.'\', \'-1\', this); return false" title="Unfortunately not so good" class="redSmiley'.sts_select_comment($ticket_rate, "-1").'"></a>
									</div><div class="clr"></div>';
	                		endif; ?>
					</form>
                </div>
                <?php
                /*$nb_stars = intval( get_post_meta( get_the_ID(), 'movie_rating', true ) );
                for ( $star_counter = 1; $star_counter <= 5; $star_counter++ ) {
                    if ( $star_counter <= $nb_stars ) {
                        echo '<img src="' . plugins_url( 'Movie-Reviews/images/icon.png' ) . '" />';
                    } else {
                        echo '<img src="' . plugins_url( 'Movie-Reviews/images/grey.png' ). '" />';
                    }
                }*/
                ?>
                <div class="subject-wrapper">
                	<span><?php echo __("Subject"); ?></span>
                	<h1 class="entry-title"><?php echo $title; ?></h1>
                </div>
			</header>

			<div class="entry-content">
				<?php 
				if($selected_option!="CLOSED") {
					comments_template(); 
				}
				?>	
				<h3 class="replies-title"><?php echo __("Replies:"); ?></h3>
				<?php
					$comments = get_comments('post_id='.get_the_ID());
					if(count($comments)>0) : echo '<div class="please-rate">Please take a second to rate my reply..</div>'; endif;
					foreach($comments as $comment) :
						$comment_rating = get_comment_meta( $comment->comment_ID, "sts_comment_rate", true );
						echo '<div class="a-comment" id="comment-'.$comment->comment_ID.'">
								<div class="top-bar">
									<div class="author-name">';
									echo($comment->comment_author);
									echo('<span class="date"> '.$comment->comment_date.'</span>');
						echo '		</div>
									<div class="smillies">
										<a href="#" onclick="sts_rate_comment(\''.$comment->comment_ID.'\', \''.$post->ID.'\', \''.$ticketID.'\', \'1\', this); return false" title="It was a great experience" class="greenSmiley'.sts_select_comment($comment_rating, "1").'"></a>
										<a href="#" onclick="sts_rate_comment(\''.$comment->comment_ID.'\', \''.$post->ID.'\', \''.$ticketID.'\', \'0\', this); return false" title="It was an OK experience" class="orangeSmiley'.sts_select_comment($comment_rating, "0").'"></a>
										<a href="#" onclick="sts_rate_comment(\''.$comment->comment_ID.'\', \''.$post->ID.'\', \''.$ticketID.'\', \'-1\', this); return false" title="Unfortunately not so good" class="redSmiley'.sts_select_comment($comment_rating, "-1").'"></a>
									</div>
									<div class="clr"></div>
								</div>';
								echo nl2br($comment->comment_content);
						echo '</div>';
					endforeach;
				?>
				<div class="a-comment" id="the-ticket">
					<div class="top-bar">
						<div class="author-name">
							<?php echo get_post_meta( get_the_ID(), 'sts_ticket_author', true ); ?>
							<span class="date"><?php echo $post->post_date; ?></span>
						</div>
						<div class="clr"></div>
					</div>
					<?php the_content(); ?>
					<div class="attachments">
						<?php
							$args = array( 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' =>'any', 'post_parent' => $post->ID ); 
							$attachments = get_posts( $args );
							if ( $attachments ) {
								echo "<hr></hr>";
								echo "<ul>";
								foreach ( $attachments as $attachment ) {
									echo "<li>";
										echo '<a href="'.$attachment->guid.'" target="_blank">'.str_replace($ticketID."-", "", $attachment->post_name).'.'.pathinfo($attachment->guid, PATHINFO_EXTENSION).'</a>';
									echo "</li>";
								}
								echo "</ul>";
							}
						?>
					</div>
				</div>
			</div><!-- .entry-content -->
			<?php
			if(get_option("sts_powerdby_display")=="1") : ?>
				<div class="sts-ticket-footer" style="font-size:70%;text-align:right">created by <a href="http://www.memoria.se">Memoria Konsulting</a></div>
			<?php endif; ?>
			<footer class="entry-meta">
				<?php edit_post_link( __( 'Edit', 'example' ), '<span class="edit-link">', '</span>' ); ?>
			</footer><!-- .entry-meta -->
			<?php } else { ?>
				<h1>Ticket could not be found</h1>
				<p>Login or check your email for the link to your ticket</p>
			<?php } ?>
		</article><!-- #post -->
    <?php endwhile; ?>
    </div>
</div>
<?php wp_reset_query(); ?>
<?php get_footer(); ?>