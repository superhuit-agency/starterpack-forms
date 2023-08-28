<?php

namespace SUPT\StarterpackForms;

use function SUPT\StarterpackForms\Type\get_form_fields;
use function SUPT\StarterpackForms\Type\get_form_opt_ins;
use function SUPT\StarterpackForms\Type\get_form_strings;

add_filter( 'spck_blockparser_format_block-supt_form', __NAMESPACE__.'\format_block', 10, 2 );


function format_block( $block, $parser ) {
	if ( empty($block['attrs']['id']) ) return $block;

	$block['attrs']['fields'] = get_form_fields([ 'id' => $block['attrs']['id'] ]);
	$block['attrs']['opt_ins'] = get_form_opt_ins([ 'id' => $block['attrs']['id'] ]);
	$block['attrs']['strings'] = get_form_strings([ 'id' => $block['attrs']['id'] ]);

	return $block;
}
