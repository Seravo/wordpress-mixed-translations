<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package tdwriter
 */

if ( ! function_exists( 'tdwriter_content_nav' ) ) :
/**
 * Display navigation to next/previous pages when applicable
 *
 * @since tdwriter 1.0
 */
function tdwriter_content_nav( $nav_id ) {
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = 'site-navigation paging-navigation';
	if ( is_single() )
		$nav_class = 'site-navigation post-navigation';

	?>
	<nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
	
	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<i class="icon-chevron-left"></i><span class="meta-nav">' . _x( '', 'Previous post link', 'tdwriter' ) . '%title</span>' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', '<span class="meta-nav">%title' . _x( '', 'Next post link', 'tdwriter' ) . '</span> <i class="icon-chevron-right"></i>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( '<i class="icon-chevron-left"></i><span class="meta-nav"> Older posts</span>', 'tdwriter' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( '<span class="meta-nav">Newer posts </span><i class="icon-chevron-right"></i>', 'tdwriter' ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>
	
	</nav><!-- #<?php echo $nav_id; ?> -->
	<?php
}
endif; // tdwriter_content_nav

if ( ! function_exists( 'tdwriter_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since tdwriter 1.0
 */
function tdwriter_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'tdwriter' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'tdwriter' ), ' ' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			
			<div class="comment-author vcard">
				<div class="avatar-container">
					<?php echo get_avatar( $comment, 96 ); ?> 
				</div>
				
				<div class="comment-author-name-date">
					<div><?php printf( __( '%s', 'tdwriter' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?></div> 
					<a class="commentmetadata" href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
						<time datetime="<?php comment_time( 'c' ); ?>">
							<?php
							setlocale(LC_TIME, get_locale());
							/* translators: 1: date, 2: time */
							printf( __( '%1$s at %2$s', 'tdwriter' ), strftime('%B %e, %Y', get_comment_date('U')), get_comment_time() ); 
							?>
						</time>
					</a>
					<span class="reply-rep">//</span>
					<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</div>
			</div>		
	
			<div class="comment-content"><?php comment_text(); ?></div>
			
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif; // ends check for tdwriter_comment()

if ( ! function_exists( 'tdwriter_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function tdwriter_posted_on() {
	printf( __( '<i class="icon-time"></i> <a href="%1$s" title="%2$s" rel="bookmark"><time datetime="%3$s">%4$s</time></a>', 'tdwriter' ),
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);
}
endif;

/**
 * Returns true if a blog has more than 1 category
 */
function tdwriter_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so tdwriter_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so tdwriter_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in tdwriter_categorized_blog
 */
function tdwriter_category_transient_flusher() {
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'tdwriter_category_transient_flusher' );
add_action( 'save_post', 'tdwriter_category_transient_flusher' );

/**
 * Returns the URL from the post.
 *
 * @uses get_the_link() to get the URL in the post meta (if it exists) or
 * the first link found in the post content.
 *
 * Falls back to the post permalink if no URL is found in the post.
 *
 * @since tdwriter 1.0
 */
function tdwriter_get_link_url() {
	if( function_exists( 'get_the_post_format_url' ) ) {
		$has_url = get_the_post_format_url();
	} else {
		$has_url = '';
	}

	return ( $has_url ) ? $has_url : apply_filters( 'the_permalink', get_permalink() );
}

/**
 * Returns Gallery Content
 *
 * @since tdwriter 1.0
 */
 function tdwriter_gallery_content( $content ) {
 	return wpautop( preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content ) );
 }

/**
 * Returns Quote Content
 *
 * @since tdwriter 1.0
 */
 function tdwriter_get_quote_content() {
 	$tdwriter_quote_source = get_post_meta( get_the_ID(), '_format_quote_source_name', true );
 	$tdwriter_quote_source_link = get_post_meta( get_the_ID(), '_format_quote_source_url', true );
 	
 	if( $tdwriter_quote_source != '' && $tdwriter_quote_source_link != '' ) {
 		$tdwriter_quote_caption = '
 									<figcaption class="quote-caption">
 										<a href="'. esc_url( $tdwriter_quote_source_link ) .'" target="_blank">' . $tdwriter_quote_source . '</a>
 									</figcaption>
 								';
 	} else if( $tdwriter_quote_source != '' && $tdwriter_quote_source_link == '' ) {
 		$tdwriter_quote_caption = '
 									<figcaption class="quote-caption">' . $tdwriter_quote_source . '</figcaption>
 								';
 	} else {
 		$tdwriter_quote_caption = '';
 	}
 	
 	echo '
 			<figure class="quote">
 				<blockquote>
 					&ldquo; '. get_the_content() .' &rdquo;
 				</blockquote>
 				'. $tdwriter_quote_caption .'
 			</figure>
 	     ';
 }
 
/**
* Post Meta (Tags and Categories)
*
* @since tdwriter 1.0
*/
function tdwriter_entry_meta() {
		
	$categories_list = get_the_category_list( __( ', ', 'tdwriter' ) );
	$tag_list = get_the_tag_list( '', __( ', ', 'tdwriter' ) );
	
	if ( $categories_list ) {
		echo '<div class="entry-categories"><span class="entry-categories-title"><i class="icon-th-list"></i> Kategori:</span> ' . $categories_list . '</div>';
	}

	if ( $tag_list ) {
		echo '<div class="entry-tags"><span class="entry-tags-title"><i class="icon-tags"></i> Nyckelord:</span> ' . $tag_list . '</div>';
	}
}

/**
 * Post Categories 
 *
 * @since tdwriter 1.0.4
 */
function tdwriter_categories() {
	$category_list = get_the_category_list( __( ', ', 'tdwriter' ) );
	if ( $category_list ) {
		echo '<i class="icon-th-list"></i>' . $category_list;
	}
}
