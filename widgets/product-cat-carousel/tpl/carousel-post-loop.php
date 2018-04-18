<?php
/**
 * @var get_categories $categories
 * @var string $default_thumbnail
 */
foreach( $categories as $cat ): ?>
	<li class="sow-carousel-item<?php if( is_rtl() ) echo ' rtl' ?>">
		<div class="sow-carousel-thumbnail">
			<?php 
				$thumbnail_id = absint(get_woocommerce_term_meta($cat->term_id, 'thumbnail_id', true));
				$image = $default_thumbnail;
				if ($thumbnail_id) 
				{
					$image = wp_get_attachment_thumb_url($thumbnail_id);
					echo '<a href="'. get_term_link($cat) .'" style="background-image: url('.sow_esc_url($image).')">';
						echo '<span class="overlay"></span>';
					echo '</a>';
				}
				else
				{
					echo '<a href="' . get_term_link($cat) . '" class="sow-carousel-default-thumbnail" ';
					if(!empty($default_thumbnail))
					{
						echo 'style="background-image: url(' . sow_esc_url($image) . ')">';
					}
						echo '<span class="overlay"></span>';
					echo '</a>';
				}
			?>
		</div>
		<h3><a href="<?php echo get_term_link( $cat ) ?>"><?php echo $cat->name; ?></a></h3>
	</li>
<?php endforeach; ?>