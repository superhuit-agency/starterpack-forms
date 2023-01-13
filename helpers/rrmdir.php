<?php

namespace SUPT\StarterpackForms\Helpers;


/**
 * Delete files & folder recursively
 * including the given directory
 *
 * @source https://stackoverflow.com/a/11267139/5078169
 */
function rrmdir($directory) {
	foreach(glob("{$directory}/*") as $file) {
		if(is_dir($file)) rrmdir($file);
		else unlink($file);
	}
	rmdir($directory);
}
