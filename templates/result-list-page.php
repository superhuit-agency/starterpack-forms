<?php // Context data are wrapped inside $args variable ?>
<div class="wrap form-submissions-list">
	<h1 class="wp-heading-inline"><?php _ex('Forms Submissions', 'SUPT Form Submissions', 'spckforms'); ?></h1>
	<hr class="wp-header-end">

	<form id="form-submissions-list-forms-select" action="<?php echo admin_url(); ?>/edit.php" class="tablenav top">
		<input type="hidden" name="post_type" value="form">
		<input type="hidden" name="page" value="form_sent">
		<div class="alignleft actions">
			<label for="form-submissions-list-forms-select-top" class="screen-reader-text"><?php _ex('Select a Form', 'SUPT Form Submissions', 'spckforms'); ?></label>
			<select name="form" id="form-submissions-list-forms-select-top">
				<option value="-1"><?php _ex('Select a Form', 'SUPT Form Submissions', 'spckforms'); ?></option>
				<?php foreach ($args['forms'] as $item) : ?>
					<?php printf( '<option value="%s"%s>%s (%d)</option>', $item['id'], ($item['active'] ? ' selected' : ''), $item['title'], $item['count'] ); ?>
				<?php endforeach; ?>
			</select>
			<input type="submit" class="button action" value="<?php _ex('Show Submissions', 'SUPT Form Submissions', 'spckforms'); ?>">
		</div>
	</form>
	<?php if ( isset($args['form']) ) : ?>
		<h2><?php echo $args['form']['title']; ?></h2>
		<form action="<?php echo admin_url(); ?>/edit.php" class="tablenav top">
			<input type="hidden" name="post_type" value="form">
			<input type="hidden" name="page" value="form_sent">
			<input type="hidden" name="form" value="<?php echo $args['form']['id']; ?>">
			<div class="alignleft actions bulkactions">
				<?php if ( $args['user_can_export'] ) : ?>
					<input type="submit" name="export-results" class="button button-primary button-export" value="<?php _ex('Export All', 'Export all results', 'spckforms'); ?>"/>
				<?php endif; ?>
				<?php if ( $args['user_can_delete'] ) : ?>
					<input type="submit" name="delete-results" class="button button-delete" value="<?php _ex('Delete All', 'Delete all results', 'spckforms'); ?>"/>
				<?php endif; ?>
			</div>
			<?php if ( isset($args['months']) && count($args['months']) > 1 ) : ?>
				<div class="alignleft actions">
					<select name="form-submissions-list-from" id="form-submissions-list-forms-filter-date_from">
						<option value=""><?php _ex('From: ', 'SUPT Form Submissions', 'spckforms'); ?></option>
						<?php foreach ($args['months'] as $ts) : ?>
							<?php printf( '<option value="%s"%s>%s</option>', $ts, ($args['results_from'] === $ts ? ' selected' : ''), date('M Y', $ts ) ); ?>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="form-submissions-list-forms-filter-date_from"><?php _ex('From', 'SUPT Form Submissions', 'spckforms'); ?></label>
					<select name="form-submissions-list-until" id="form-submissions-list-forms-filter-date_until">
						<option value=""><?php _ex("Until", 'SUPT Form Submissions', 'spckforms'); ?></option>
						<?php foreach ( array_reverse($args['months']) as $ts) : ?>
							<?php printf(
								'<option value="%s"%s>%s</option>',
								$ts,
								($args['results_until'] === $ts ? ' selected' : ''),
								date('M Y', $ts )
							); ?>
						<?php endforeach; ?>
					</select>
					<label class="screen-reader-text" for="form-submissions-list-forms-filter-date_until"><?php _ex("Until", 'SUPT Form Submissions', 'spckforms'); ?></label>
					<input type="submit" name="filter_action" id="post-query-submit" class="button" value="<?php _ex('Filter', 'SUPT Form Submissions', 'spckforms'); ?>">
				</div>
			<?php endif; ?>

			<div class="tablenav-pages one-page">
				<?php $nb_results = count($args['form']['results']); ?>
				<?php if ( $nb_results ) : ?>
					<span><?php printf( _n('1 submission', '%d submissions', $nb_results, 'SUPT Form Submissions', 'spckforms'), $nb_results ); ?></span>
				<?php endif; ?>
			</div>
			<br class="clear">
		</form>

		<div id="form-submissions-list-form_<?php echo $args['form']['id']; ?>" class="table-wrapper" method="post" data-form-sent>
			<h3 class="screen-reader-text"><?php _ex('Submissions', 'SUPT Form Submissions', 'spckforms'); ?></h3>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr>
						<th scope="col" id="date" class="manage-column column-date"><?php _ex('Date', 'SUPT Form Submissions', 'spckforms'); ?></th>
						<?php foreach ( $args['form']['fields'] as $field ) : ?>
							<?php if ( $field['type'] !== 'supt/form-section-breaker' ) : ?>
								<?php printf(
									'<th scope="col" class="manage-column column-%s column-type-%s" title="%s">%s</th>',
									esc_attr($field['name']),
									esc_attr($field['type']),
									esc_attr($field['label']),
									$field['label']
								); ?>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody id="the-list" class="ui-sortable">
					<?php foreach ( $args['form']['results'] as $itemId => $item) : ?>
						<tr id="sent-<?php echo $itemId; ?>" class="hentry">
							<td class="date column-date has-row-actions column-primary" data-colname="<?php _ex('Date', 'SUPT Form Submissions', 'spckforms'); ?>">
								<strong><?php echo date('d.m.Y @ H:i', $item['timestamp']); ?></strong>
								<?php if ( $args['user_can_delete'] ) : ?>
									<div class="row-actions">
										<span class="trash">
											<?php printf(
												'<a href="%s" class="trash-result" aria-label="%s">%s</a>',
												admin_url()."?action=trash-submission&form={$args['form']['id']}&sent=$itemId",
												_x('Delete permanently', 'Delete result item', 'spckforms'),
												_x('Delete permanently', 'Delete result item', 'spckforms')
											); ?>
										</span>
									</div>
								<?php endif; ?>
							</td>
							<?php foreach ( $args['form']['fields'] as $field) : ?>
								<?php if ( $field['type'] !== 'supt/form-section-breaker' ) : ?>
									<td class="column-<?php echo $field['name']; ?> column-type-<?php echo $field['type']; ?>" data-colname="<?php echo $field['name']; ?>">
										<?php if ( $field['type'] === 'checkbox' ) : ?>
											<?php printf(
												'<span class="dashicons dashicons-%s"></span>',
												( (empty($item[$field['name']]) || $item[$field['name']] === 'off') ? 'no-alt' : 'yes' )
											); ?>
										<?php else : ?>
											<?php if(is_array($item[$field['name']])):
												echo join(',', $item[$field['name']]);
											else :
												echo $item[$field['name']];
											endif ?>
										<?php endif; ?>
									</td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

</div>
