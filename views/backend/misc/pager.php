<a 
	<?php if($current_page <= 1) { ?> class="urtak-disabled" data-disabled="disabled" <?php } ?>
	data-value="<?php esc_attr_e($current_page - 1); ?>" 
	id="urtak-meta-box-controls-page-previous" href="#">Previous</a>

<input data-urtak-attribute="page" id="urtak-meta-box-controls-page-number" type="text" value="<?php esc_attr_e($current_page); ?>" />
<span id="urtak-meta-box-controls-total-pages">/ <?php esc_html_e($number_pages); ?></span>

<a <?php if($current_page >= $number_pages) { ?> class="urtak-disabled" data-disabled="disabled" <?php } ?>
	data-value="<?php esc_attr_e($current_page + 1); ?>" 
	id="urtak-meta-box-controls-page-next" href="#">Next</a>