<div class="urtak-moderation">
	<div class="urtak-moderation-left">
		<div class="urtak-moderation-left-inner">
			<h4><?php _e('Questions'); ?></h4>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr valign="top">
						<td><?php _e('No questions found', 'urtak'); ?></td>
					</tr>

					<tr valign="top">
						<td><?php _e('Loading', 'urtak'); ?></td>
					</tr>

					<!-- ko: foreach question -->

					<!-- /ko -->
				</tbody>
			</table>

			<h4><?php _e('Flagged Questions'); ?></h4>

			<table class="widefat fixed">
				<thead>
					<tr valign="top">
						<th scope="col"><?php _e('Question', 'urtak'); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr valign="top">
						<td><?php _e('No questions found', 'urtak'); ?></td>
					</tr>

					<!-- ko: foreach flagged -->

					<!-- /ko -->
				</tbody>
			</table>
			<p>Flagged questions</p>
		</div>
	</div>

	<div class="urtak-moderation-right">
		<div class="urtak-moderation-right-inner">
			<h4><?php _e('Urtaks'); ?></h4>

			<p>All questions button</p>
			<p>Controls (including search / filter controls)</p>
			<p>Table of Urtaks w/ pagination</p>
		</div>
	</div>

	<div class="urtak-clear"></div>
</div>