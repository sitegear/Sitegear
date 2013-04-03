<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default settings for Sitegear Page Messages module.
 */
return array(

	/**
	 * Key used in the session for page messages array.
	 */
	'session-key' => 'page-messages',

	/**
	 * Settings for components.
	 */
	'components' => array(

		/**
		 * Settings for the messages component.
		 */
		'messages' => array(
			'outer-element' => 'ul',
			'outer-attributes' => array(
				'class' => 'sitegear-page-messages'
			),
			'inner-element' => 'li',
			'inner-attributes' => array()
		)
	)
);
