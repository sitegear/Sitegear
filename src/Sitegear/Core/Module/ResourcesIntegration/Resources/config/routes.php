<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Route definitions for Sitegear Resources Integration module.
 */
return array(
	'resource' => array(
		array( 'name' => 'location' ),
		array( 'name' => 'path', 'requirements' => '.+' )
	)
);
