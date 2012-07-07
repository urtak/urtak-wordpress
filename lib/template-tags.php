<?php

/**
 * Returns the embed code needed to embed a particular urtak
 * widget. A variety of arguments can be provided in standard
 * WordPress form:
 *
 * @param array $args
 * @see Urtak::get_embeddable_widget
 */
function urtak_get_embeddable_widget($args = array()) {
	return apply_filters('urtak_get_embeddable_widget', UrtakPlugin::get_embeddable_widget($args), $args);
}

/**
 * Echoes the return value of urtak_get_embeddable_widget. This
 * template tag also provides a filter on that returned value in
 * case the output code needs to be modified in some way from the
 * returned code.
 *
 * @param array $args
 * @see urtak_get_embeddable_widget
 */
function urtak_the_embeddable_widget($args = array()) {
	echo apply_filters('urtak_the_embeddable_widget', urtak_get_embeddable_widget($args), $args);
}

/**
 * This is a legacy template tag and simply delegates off
 * to the new style template tag which better reflects
 * WordPress conventions around template tag naming.
 *
 * @param array $args
 * @see urtak_the_embeddable_widget
 */
function make_urtak_widget($args = array()) {
	urtak_the_embeddable_widget($args);
}

// We do this so people can insert the action into their template
// instead of a function_exists(...) { ... } construct
add_action('make_urtak_widget', 'make_urtak_widget');

function urtak_the_responses_number($post_id = null) {
	if(empty($post_id)) {
		$post_id = get_the_ID();
	}

	printf('<a href="%s#embedded-urtak-%d" data-post-id="%d" class="urtak-responses-number"><span class="urtak-responses-number-interior">...</span></a>', get_permalink($post_id), $post_id, $post_id);
}