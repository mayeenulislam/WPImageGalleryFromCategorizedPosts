<?php
/*
Template name: News Image Gallery
*/
?>

<?php get_header(); ?>

    <div class="row">

        <content>
            <div id="content">

            <?php
            date_default_timezone_set('Asia/Dhaka'); //globalizing the timezone first
            $dateToday = date("Y-m-d H:i:s");
            $strDateToday = strtotime( $dateToday );
            ?>

			<?php
			global $wpdb;
			$wpdb->show_errors();
			$tablePosts = $wpdb->prefix . "posts";
			$tableTaxonomyRel = $wpdb->prefix . "term_relationships";

			/**
			*	PAGINATION IN ACTION
			*	Source: http://tareq.wedevs.com/2011/07/simple-pagination-system-in-your-wordpress-plugins/
			*/

			$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

			$limit = 30;
			$offset = ( $pagenum - 1 ) * $limit;

			/** Query for all the attachments
			*		get only images (jpg,png,gif,tiff)
			*		which are included into posts (and categorized)
			*		exclude images of Post category "Uncategorized" (id = 1)
			*		check whether matches with our defined categories
			*/

			$definedCats = "2,3,4,5,6,7";

			$gal_query = $wpdb->get_results(
		        "SELECT
		        	$tablePosts.ID,
		        	$tablePosts.post_type,
		        	$tablePosts.post_parent,
		        	$tablePosts.guid,
		        	$tablePosts.post_date,
		        	$tablePosts.post_mime_type,
		        	$tableTaxonomyRel.object_id,
		        	$tableTaxonomyRel.term_taxonomy_id
			    FROM $tablePosts, $tableTaxonomyRel
			    WHERE $tablePosts.post_type = 'attachment' AND $tablePosts.post_parent = $tableTaxonomyRel.object_id
			       	AND (
			       		post_mime_type = 'image/jpeg'
			       		OR post_mime_type = 'image/png'
			       		OR post_mime_type = 'image/gif'
			       		OR post_mime_type = 'image/tiff'
			       		)
		    	   	AND ($tableTaxonomyRel.term_taxonomy_id != '1')
		          	AND $tableTaxonomyRel.term_taxonomy_id IN ( $definedCats )
		        GROUP BY post_date DESC
		        LIMIT $offset, $limit
		        ");
		    ?>

		    <?php
		    /**
		    *	SHOW the today's Gallery
		    *	only on the first page
		    */
		    if( $pagenum == 1 ) { ?>

			<h2 class="gallery-secondary-title">ছবিতে আজকের দিন</h2>

			<!--  Outer wrapper for presentation only, this can be anything you like -->
			<div id="banner-fade" class="gallery-today-slider">

				<!-- start Basic Jquery Slider -->
				<ul class="bjqs">
				<?php foreach ($gal_query as $gallery) { ?>
					<?php
					//Getting the posts' individual category
					$postID = $gallery->post_parent;
					$postCategory = get_the_category( $postID );

					//Date things
					$gotDate = $gallery->post_date;
					$strGotDate = strtotime($gotDate);

					$attachmentID = $gallery->ID; // Image/Attachment ID
					$imageURL = $gallery->guid; // Original Image URL

					$thumbURL = wp_get_attachment_medium_url( $attachmentID );
					$imageCaption = get_post( $attachmentID )->post_excerpt; //caption
					$postTitle = get_the_title( $postID ); //news title
					?>

					<?php if( $strGotDate >= $strDateToday ) { ?>
					<li>
						<a href="<?php echo get_permalink( $postID ); ?>" title="<?php echo $postTitle; ?>">
							<img src="<?php echo $imageURL ?>" alt="<?php echo $postTitle; ?>" title="<?php echo $imageCaption ? $imageCaption : $postTitle; ?>"/>
						</a>
					</li>
					<?php } else { ?>
					<li>
						<a href="<?php echo get_permalink( $postID ); ?>" title="<?php echo $postTitle; ?>">
							<img src="<?php echo $imageURL ?>" alt="<?php echo $postTitle; ?>" title="<?php echo $imageCaption ? $imageCaption : $postTitle; ?>"/>
						</a>
					</li>
					<?php } //endif( $strGotDate >= $strDateToday ) ?>

				<?php } //endforeach ?>
				</ul>
				<!-- end Basic jQuery Slider -->

			</div>
			<!-- End outer wrapper -->

			<script class="secret-source">
			jQuery(document).ready(function($) {

			  $('#banner-fade').bjqs({
			    height      : 400,
			    width       : 706,
			    responsive  : true,
			    showmarkers : false,
			    nexttext : 'পরের ছবি',
			    prevtext : 'আগের ছবি',
			  });

			});
			</script>

			<?php } //endif( $pagenum == 1 ) ?>

			<h2 class="gallery-secondary-title">ছবিতে সকল সংবাদ</h2>

			<?php
			$divPlacer = 1;
			foreach ($gal_query as $gallery) {

				$one = ( $divPlacer % 3 == 1 ? ' gal-col-one' : '' );
		        $first = ( $divPlacer % 3 == 0  ? ' gal-col-third' : '' );

				//Getting the posts' individual category
				$postID = $gallery->post_parent;
				$postCategory = get_the_category( $postID );

				$attachmentID = $gallery->ID; // Image/Attachment ID
				$imageURL = $gallery->guid; // Original Image URL

				$thumbURL = wp_get_attachment_thumb_url( $attachmentID ); //thumbnail of the original image
				$imageCaption = get_post( $attachmentID )->post_excerpt; //caption
				$postTitle = get_the_title( $postID ); //news title
					?>
				<div class="gallery-thumb <?php echo $one; echo $first; ?>">
					<a href="<?php echo $imageURL; ?>" title="<?php echo $postTitle; ?>">
						<?php echo '<img src="'. $thumbURL .'" alt="'. $postTitle .'"/>'; ?>
					</a>
					<div class="gallery-image-caption">
						<?php echo $imageCaption ? $imageCaption : $postTitle; ?>
					</div> <!-- .gallery-image-caption -->
					<a class="gallery-grid-footer" href="<?php echo get_permalink( $postID ); ?>" title="পড়ুন: <?php echo $postTitle; ?>">
	                    <?php _e('মূল সংবাদ পড়ুন', 'vpata'); ?>
	                </a> <!-- .gallery-grid-footer -->
				</div> <!-- .gallery-thumb -->
				<?php
			$divPlacer++;
			} //endforeach

			?>

			<?php

			$newQuery = $wpdb->get_results(
		        "SELECT
		        	$tablePosts.post_type,
		        	$tablePosts.post_parent,
		        	$tablePosts.post_date,
		        	$tablePosts.post_mime_type,
		        	$tableTaxonomyRel.object_id,
		        	$tableTaxonomyRel.term_taxonomy_id
			    FROM $tablePosts, $tableTaxonomyRel
			    WHERE $tablePosts.post_type = 'attachment' AND $tablePosts.post_parent = $tableTaxonomyRel.object_id
			       	AND (
			       		post_mime_type = 'image/jpeg'
			       		OR post_mime_type = 'image/png'
			       		OR post_mime_type = 'image/gif'
			       		OR post_mime_type = 'image/tiff'
			       		)
		    	   	AND ($tableTaxonomyRel.term_taxonomy_id != '1')
		          	AND $tableTaxonomyRel.term_taxonomy_id IN ( $definedCats )
		        GROUP BY post_date DESC
		        ");

			$total = count($newQuery);
			$num_of_pages = ceil( $total / $limit );

			$page_links = paginate_links( array(
			    'base' => add_query_arg( 'pagenum', '%#%' ),
			    'format' => '',
			    'prev_text' => __( '&laquo;', 'aag' ),
			    'next_text' => __( '&raquo;', 'aag' ),
			    'total' => $num_of_pages,
			    'current' => $pagenum
			) );
			 
			if ( $page_links ) { ?>
			    <div class="news-gallery-navigation clearfix go-right">
			    	<div class="news-gallery-pages" style="margin: 1em 0"><?php echo $page_links; ?></div>
			    </div>
			<?php } ?>

            </div> <!-- #content -->
        </content>

        <?php get_sidebar(); ?>

    </div> <!-- .row -->

<?php get_footer(); ?>