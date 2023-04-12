<?php // Context data are wrapped inside $args variable ?>
<div style="max-width: 800px; margin: 0 auto; font-family: Open Sans, Helvetica, Arial, sans-serif;">
	<?php if (!empty($args['site']['logo'])) : ?>
	<div>
		<img
			width="<?php echo $args['site']['logo']['width']; ?>"
			height="<?php echo $args['site']['logo']['height']; ?>"
			src="<?php echo $args['site']['logo']['src']; ?>"
			alt="<?php echo $args['site']['logo']['alt']; ?>"
			style="margin: 20px auto; display: block; width:<?php echo $args['site']['logo']['width']; ?>px; height:<?php echo $args['site']['logo']['height']; ?>px;"
		>
	</div>
	<?php endif; ?>
	<h2 style="text-align: center;"><?php
		printf(
			// translators: %s is the name of the form
			_x( 'Form submission from %s', 'Form type email notification', 'spckforms'),
			sprintf(
				'<a href="%s" target="_blank">%s</a>',
				$args['form']['link'],
				$args['form']['title']
			)
		);
	?></h2>
	<div>
	<?php foreach ($args['data'] as $field) : ?>
		<div style="padding: 10px 20px; border-bottom: 1px solid #ccc;<?php if ($field['i'] % 2 === 0) : ?> background-color: #f5f5f5;<?php endif; ?>">
			<strong><?php echo strip_tags( !empty($field['legend']) ? $field['legend'] : $field['label'] ); ?></strong>
			<div style="margin-top: 5px; white-space: pre-line;">
				<?php if ($field['type'] === 'file') : ?>
					<?php foreach ($field['value'] as $file) : ?>
						<?php printf( '<a href="%s" target="_blank">%s (%s)</a>', $file['url'], $file['name'], $file['size'] ); ?>
					<?php endforeach ?>
				<?php else : ?>
					<?php echo $field['value']; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
	</div>
</div>
