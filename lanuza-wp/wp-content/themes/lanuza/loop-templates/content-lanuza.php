<?php
/**
 * Partial template for content in page.php
 *
 * @package understrap
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php echo get_the_post_thumbnail( $post->ID, 'full' ); ?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

<div class="container">
    <div class="row">
    <div class="col-sm-8 offset-sm-2"><header class="entry-header">

<?php the_title( '<h1 class="entry-title text-center">', '</h1>' ); ?>

</header><!-- .entry-header -->



<div class="entry-content">

<?php the_content(); ?>

<?php
wp_link_pages(
    array(
        'before' => '<div class="page-links">' . __( 'Pages:', 'understrap' ),
        'after'  => '</div>',
    )
);
?>

</div><!-- .entry-content -->

<footer class="entry-footer">

<?php edit_post_link( __( 'Edit', 'understrap' ), '<span class="edit-link">', '</span>' ); ?>

</footer><!-- .entry-footer -->
</div>
</div>
</div>
	
</article><!-- #post-## -->
