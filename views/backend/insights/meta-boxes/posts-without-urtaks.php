<?php if($posts->have_posts()) { ?>
<ul id="urtak-posts-without-urtaks-list">
	<?php while($posts->have_posts()) { $posts->the_post(); ?>
	<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php edit_post_link(__('Edit')); ?></li>
	<?php } ?>
</ul>
<?php }