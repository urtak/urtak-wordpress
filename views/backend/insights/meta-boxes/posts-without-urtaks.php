<?php if($posts->have_posts()) { ?>
<ul id="urtak-posts-without-urtaks-list">
	<?php while($posts->have_posts()) { $posts->the_post(); ?>
	<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a> - <?php edit_post_link(__('Edit', 'urtak')); ?></li>
	<?php } ?>
</ul>
<?php } ?>

<script type="text/javascript">
(function() {
	var $ = jQuery
	,$posts_without_urtaks = $('#urtak-posts-without-urtaks-list')
	, $lis = $posts_without_urtaks.find('li')
	, $link = $('<a href="#"></a>').text(Urtak_Vars.see_all)
	, $more = $('<li></li>').append($link);

	if($lis.size() > 5) {
		$lis.filter(':gt(4)').hide();	
		$posts_without_urtaks.append($more);

		$link.click(function(event) {
			event.preventDefault();

			$lis.show();
			$more.hide();
		});
	}
})();
</script>