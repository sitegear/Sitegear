<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default resources registered by the Sitegear engine.  These are available for activation anywhere.  This file is
 * intended to be included into the main defaults.php default configuration data file.
 */
return array(

	/**
	 * jQuery script.
	 *
	 * Use Google CDN; the jQuery source CDN does not support https: (certificate error).
	 */
	'script:vendor:jquery' => array(
		'type' => 'script',
		'url' => array(
			'default' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery/jquery-1.9.1.js',
			'overrides' => array(
				'development' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery/jquery-1.9.1.min.js'
			)
		),
		'cdn-url' => array(
			'default' => '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
			'overrides' => array(
				'development' => '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js'
			)
		)
	),

	/**
	 * jQueryUI script.
	 *
	 * Use Google CDN; the jQuery source CDN does not support https: (certificate error).
	 */
	'script:vendor:jquery-ui' => array(
		'type' => 'script',
		'url' => array(
			'default' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-ui/jquery-ui.js',
			'overrides' => array(
				'development' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-ui/jquery-ui.js'
			)
		),
		'cdn-url' => array(
			'default' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js',
			'overrides' => array(
				'development' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.js'
			)
		),
		'requires' => array(
			'script:vendor:jquery',
			'styles:vendor:jquery-ui'
		)
	),

	/**
	 * jQueryUI CSS (base theme).
	 *
	 * Use Google CDN; the jQuery source CDN does not support https: (certificate error).
	 */
	'styles:vendor:jquery-ui' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-ui/jquery-ui.css',
		'cdn-url' => '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css'
	),

	/**
	 * jQuery cookie plugin.
	 */
	'script:vendor:cookie' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-cookie/jquery.cookie.js',
		'cdn-url' => array(
			'default' => '//cdn.jsdelivr.net/jquery.cookie/1.3.1/jquery.cookie.js'
		),
		'requires' => array(
			'script:vendor:jquery'
		)
	),

	/**
	 * jQuery fileupload plugin.
	 */
	'script:vendor:fileupload' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-fileupload/jquery.fileupload.js',
		'requires' => array(
			'script:vendor:jquery-ui'
		)
	),

	/**
	 * jQuery iframe transport plugin, for broken browsers.
	 */
	'script:vendor:iframe-transport' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-iframe-transport/jquery.iframe-transport.js',
		'requires' => array(
			'script:vendor:jquery'
		)
	),

	/**
	 * jQuery tree view widget.
	 */
	'script:vendor:jstree' => array(
		'type' => 'script',
		'url' => array(
			'default' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jstree/jquery-jstree.js',
			'overrides' => array(
				'development' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jstree/jquery-jstree-min.js'
			)
		),
		'cdn-url' => array(
			'default' => '//cachedcommons.org/cache/jquery-jstree/1.0.0/javascripts/jquery-jstree-min.js',
			'overrides' => array(
				'development' => '//cachedcommons.org/cache/jquery-jstree/1.0.0/javascripts/jquery-jstree.js'
			)
		),
		'requires' => array(
			'script:vendor:jquery-ui'
		)
	),

	/**
	 * jQuery splitter plugin.
	 */
	'script:vendor:splitter' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-splitter/jquery.splitter.js',
		'requires' => array(
			'script:vendor:jquery-ui',
			'styles:vendor:splitter'
		)
	),

	/**
	 * Stylesheets for jQuery splitter plugin.
	 */
	'styles:vendor:splitter' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-splitter/jquery.splitter.css',
		'requires' => array(
			'styles:vendor:jquery-ui'
		)
	),

	/**
	 * jQuery cookie plugin.
	 */
	'script:vendor:toolbar' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-toolbar/jquery.ui.toolbar.js',
		'requires' => array(
			'script:vendor:jquery-ui',
			'styles:vendor:toolbar'
		)
	),

	/**
	 * Stylesheets for jQuery splitter plugin.
	 */
	'styles:vendor:toolbar' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/vendor/jquery-toolbar/jquery.ui.toolbar.css',
		'requires' => array(
			'styles:vendor:jquery-ui'
		)
	),

	/**
	 * Sitegear utilities script.
	 */
	'script:sitegear:utilities' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/utilities/sitegear.utilities.js',
		'requires' => array(
			'script:vendor:jquery-ui',
			'styles:sitegear:utilities'
		)
	),

	/**
	 * Stylesheets for Sitegear utilities script.
	 */
	'styles:sitegear:utilities' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/utilities/sitegear.utilities.css',
		array(
			'styles:vendor:jquery-ui'
		)
	),

	/**
	 * Sitegear management tools base functionality.
	 */
	'script:sitegear:base' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/base/sitegear.base.js',
		'requires' => array(
			'script:sitegear:utilities',
			'styles:sitegear:base'
		)
	),

	/**
	 * Sitegear management tools base stylesheet.
	 */
	'styles:sitegear:base' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/base/sitegear.base.css',
		array(
			'styles:sitegear:utilities'
		)
	),

	/**
	 * Cookie dialog (remembers its position and size using a persistent cookie).
	 */
	'script:sitegear:cookie-dialog' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/cookie-dialog/sitegear.cookie-dialog.js',
		'requires' => array(
			'script:vendor:jquery-ui',
			'script:vendor:cookie'
		)
	),

	/**
	 * Tree panel dialog.
	 */
	'script:sitegear:tree-panel-dialog' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/tree-panel-dialog/sitegear.tree-panel-dialog.js',
		'requires' => array(
			'script:vendor:cookie-dialog',
			'script:vendor:splitter',
			'script:sitegear:base'
		)
	),

	/**
	 * Data editor widget.
	 */
	'script:sitegear:data-editor' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/data-editor/sitegear.data-editor.js',
		'requires' => array(
			'script:sitegear:base',
			'styles:sitegear:data-editor'
		)
	),

	/**
	 * Stylesheets for data editor widget.
	 */
	'styles:sitegear:data-editor' => array(
		'type' => 'styles',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/data-editor/sitegear.data-editor.css',
		'requires' => array(
			'styles:vendor:jquery-ui'
		)
	),

	/**
	 * Command dialog -- root management widget.
	 */
	'script:sitegear:command-dialog' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/command-dialog/sitegear.command-dialog.js',
		'requires' => array(
			'script:vendor:cookie-dialog',
			'script:sitegear:base'
		)
	),

	/**
	 * WYSIWYG content editor.
	 */
	'script:sitegear:content-editor' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/content-editor/sitegear.content-editor.js',
		'requires' => array(
			'script:vendor:toolbar'
		)
	),

	/**
	 * File manager.
	 */
	'script:sitegear:file-manager' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/file-manager/sitegear.file-manager.js',
		'requires' => array(
			'script:vendor:tree-panel-dialog',
			'script:sitegear:base'
		)
	),

	/**
	 * Upload manager.
	 */
	'script:sitegear:upload-manager' => array(
		'type' => 'script',
		'url' => '{{ config:system.command-url.root }}/{{ config:system.command-url.resources }}/resource/engine/upload-manager/sitegear.upload-manager.js',
		'requires' => array(
			'script:vendor:jquery',
			'script:vendor:jquery-ui',
			'script:vendor:cookie',
			'script:vendor:cookie-dialog',
			'script:sitegear:base',
			'script:sitegear:utilities'
		)
	)

);
