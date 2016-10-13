<?php

namespace UncleCheese\Green;

use \Image_Cached;
use \DataExtension;

class SerialisedDBFieldExtension extends DataExtension
{
	public function updateCastingHint(&$dbField, $hint, $value)
	{
		if($hint === 'Image') {
			$dbField = new Image_Cached($value);
		}
	}
}