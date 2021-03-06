<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

/**
 * Default engine settings for Sitegear.
 */
return array(

	/**
	 * These values should be provided as a minimum by the site configuration.  Each one corresponds to a method in
	 * SiteInfoProviderInterface, which is part of EngineInterface.
	 */
	'site' => array(

		/**
		 * Site identifier, used whenever the site needs to be uniquely identified against other sites or apps (e.g.
		 * session namespaces).
		 */
		'id' => null,

		/**
		 * Site name, for display purposes.
		 */
		'display-name' => null,

		/**
		 * Site logo URL, relative to the site root, for display within the content management tools.
		 */
		'logo-url' => null,

		/**
		 * Site administrator name and email address.
		 */
		'administrator' => array(
			'name' => null,
			'email' => null
		),

		/**
		 * Site email addresses -- associative array.
		 */
		'email' => array()
	),

	/**
	 * HTTP request/response and web server related settings.
	 */
	'system' => array(

		/**
		 * Settings that control how resources and resource configuration are handled.  Not to be confused with the
		 * 'resources' key at the root level, which specifies a resource map for automatic registration.
		 */
		'resources' => array(

			/**
			 * Whether CDN delivery should be preferred over local delivery of assets within the Sitegear deployed
			 * directory structure.  This may be a boolean value, which gives the default value applied to all resource
			 * keys.  For fully granular control, specify a key-value array with two keys, 'default' which gives the
			 * default (boolean) value, and 'overrides' which gives a map where the keys are resource keys (like
			 * 'script:vendor:jquery') and the values are booleans (typically the opposite of the 'default' value).
			 */
			'prefer-cdn' => true,

			/**
			 * Path, relative to the site root, to the vendor resources package.
			 */
			'vendor-resources' => 'vendor/sitegear/vendor-resources'

		),

		/**
		 * Settings for command URLs, which are special URLs handled internally by Sitegear.
		 */
		'command-url' => array(

			/**
			 * The root command URL component.
			 */
			'root' => 'sitegear',

			/**
			 * User and authentication command URL component.
			 */
			'user' => 'user',

			/**
			 * Resource retrieval URL component.
			 */
			'resources' => 'resources'

		),

		/**
		 * Settings for handling file responses, that is responses to requests for "public" resources.  These default
		 * settings will detect if Apache2 mod_xsendfile is available, and use it if it is, otherwise will send the
		 * file contents through PHP.
		 */
		'file-response' => array(

			/**
			 * Whether or not to use the header (X-Sendfile style) method of sending file responses.  Use true to
			 * always use the header method, false to never use it (always stream using PHP), and 'detect' to
			 * automatically determine, using the 'detect-*' values, whether the required support is available.
			 *
			 * Note that in order to use the header method of sending file responses, the bootstrap or some other code
			 * must call BinaryFileResponse::trustXSendfileTypeHeader(), otherwise empty responses will be sent.  If
			 * this method is not called, then this configuration value should be changed to false.
			 */
			'use-header' => 'detect',

			/**
			 * The function to use to determine whether the required value is available, if 'use-header' is set to
			 * 'detect'.  It should require no arguments, and should return an array, which is searched for the
			 * 'detect-value' value.
			 */
			'detect-function' => 'apache_get_modules',

			/**
			 * The value to search for in the result of 'detect-function', if 'use-header' is set to 'detect'.
			 */
			'detect-value' => 'mod_xsendfile',

			/**
			 * The header to set if 'use-header' is set to true, or set to 'detect' and detection discovers the
			 * required support.
			 */
			'header' => 'X-Sendfile'

		),

		/**
		 * Data file containing MIME type information.
		 */
		'mime-types' => __DIR__ . '/mime.types'

	),

	/**
	 * URL to module routing configuration.
	 */
	'routes' => array(

		/**
		 * Module name to use in routes for error pages.
		 */
		'error-route-module' => 'content',

		/**
		 * URL to module mappings.  Each entry consists of a 'root' key and a 'module' key.  More specific (deeper) URL
		 * mount points take precedence over less specific (higher level) mount points.
		 *
		 * There are several pre-determined routes that cannot be directly overridden.  One of these mounts the content
		 * module to the site's root URL.  The remainder are generated by the "system.command-url" settings, and are
		 * used to provide management functionality.
		 */
		'map' => array()
	),

	/**
	 * URL to template (page-level view script) mapping configuration.
	 */
	'templates' => array(

		/**
		 * Template to use for error pages.
		 */
		'error-template' => 'default',

		/**
		 * URL to template mappings.  Each entry consists of a "pattern" key and a "template" key.
		 *
		 * The "pattern" key matches the URL against a pattern using ? * + ! wildcards.  Alternatively, by providing a
		 * "regex" key with a true value, the "pattern" key is a complete regular expression.  In case of multiple
		 * patterns matching the same URL, the later entry is always preferred.
		 *
		 * The default configuration maps all URLs to the default template.
		 */
		'map' => array(
			array(
				'pattern' => '!',
				'template' => 'default'
			)
		)
	),

	/**
	 * URL to protocol scheme mapping configuration.  This enables automatic redirection when the protocol does not
	 * match requirements.
	 */
	'protocols' => array(

		/**
		 * The default protocol.  In general this should not be used unless you want to force all requests over https.
		 * A null value indicates no protocol switching is done by default.
		 */
		'default' => null,

		/**
		 * List of URL pattern to protocol mappings.  Each entry consists of a "pattern" key and a "protocol" key,
		 * which has the value 'http' or 'https', or null to indicate that the protocol should never be switched.
		 *
		 * The "pattern" key matches the URL against a pattern using ? * + ! wildcards.  Alternatively, by providing a
		 * "regex" key with a true value, the "pattern" key is a complete regular expression.  In case of multiple
		 * patterns matching the same URL, the later entry is always preferred.
		 */
		'map' => array()

	),

	/**
	 * URL redirection settings.
	 */
	'redirect' => array(

		/**
		 * Default redirection method.  Allowed values are "redirect" (issue a 301 redirect) or "alias" (show identical
		 * content under multiple URLs without using a redirect),
		 */
		'default' => 'redirect',

		/**
		 * URL redirection and alias map.  Each entry is an associative array with "pattern" and "url" keys.  The
		 * "pattern" key matches the URL against a pattern using ? * + ! wildcards.  Alternatively, by providing a
		 * "regex" key with a true value, the "pattern" key is a complete regular expression.  The "url" key can be
		 * relative or fully qualified, including to other domains etc.  The optional "type" key specifies either
		 * "redirect" or "alias".  If omitted, the value of the "redirect.default" setting is used.
		 */
		'map' => array(),

	),

	/**
	 * Settings used internally by the engine.  In general these do not need to be changed.
	 */
	'engine' => array(

		/**
		 * Configuration related to module creation and management.
		 */
		'modules' => array(

			/**
			 * Key-value array of module name to fully qualified class name.  Each class must implement
			 * \Sitegear\Module\ModuleInterface
			 */
			'class-map' => array(),

			/**
			 * List of namespaces within which module names should be searched using the standard pattern:
			 * "[Namespace]\[ModuleName]\[ModuleName][Suffix]" (where [Suffix] is given by class-name-suffix below).
			 * If the module name is specified in 'class-map', that will take precedence.  All namespaces should be
			 * absolute, beginning with a leading backslash "\" character.
			 */
			'namespaces' => array(
				'\\Sitegear\\Module'
			),

			/**
			 * Prefix that is expected at the beginning of all Module names using the standard pattern, i.e. within the
			 * namespaces specified above.
			 */
			'class-name-prefix' => '',

			/**
			 * Suffix that is expected at the end of all Module names using the standard pattern, i.e. within the
			 * namespaces specified above.
			 */
			'class-name-suffix' => 'Module'

		),

		/**
		 * Module resolution of specific modules (see \Sitegear\Module\ModuleResolverInterface).
		 */
		'module-resolution' => array(

			/**
			 * Module that is provides a fallback page controller method.
			 */
			'default-controller' => 'content',

			/**
			 * Method to call in the default controller, when using it as a fallback controller,  Note that "Page" will
			 * be appended to this as per the standard naming conventions.
			 */
			'default-controller-method' => 'default',

			/**
			 * Module that provides a default rendering context..
			 */
			'default-content' => 'content',

			/**
			 * Module that provides a context for rendering error pages.  The target methods used will correspond with
			 * the HTTP status code.
			 */
			'error-content' => 'content',

			/**
			 * Sequence of bootstrap module names.  Each must implement \Sitegear\Module\BootstrapModuleInterface
			 */
			'bootstrap-sequence' => array(
				'file'
			)
		)
	),

	/**
	 * Memcache settings.
	 */
	'memcache' => array(

		/**
		 * Whether to enable Memcache.  It is strongly recommended this is set to true, it can cut page load times
		 * significantly.
		 */
		'enabled' => true,

		/**
		 * Memcache servers.  Array of key-value arrays, each key-value array has 'host' and 'port' keys.  If 'port'
		 * is omitted, the default is used (11211).
		 */
		'servers' => array(
			array(
				'host' => 'localhost',
				'port' => 11211
			)
		)
	),

	/**
	 * View configuration settings.
	 */
	'view' => array(

		/**
		 * Page rendering formats to register.
		 */
		'renderers' => array(
			'php' => '\\Sitegear\\View\\Renderer\\PhpRenderer'
			//'twig' => '\\Sitegear\\View\\Renderer\\Twig\\TwigRenderer'
		),

		/**
		 * Decorator implementations to register.  These are used by provided module, note that overriding these
		 * default keys is a powerful way of altering or breaking the core behaviour.  Generally, you should assign
		 * new keys (not listed below) to your own decorator implementations.
		 */
		'decorators' => array(
			'element' => '\\Sitegear\\View\\Decorator\\ElementDecorator',
			'inline-element' => '\\Sitegear\\View\\Decorator\\InlineElementDecorator',
			'editable' => '\\Sitegear\\View\\Decorator\\EditableDecorator',
			'excerpt' => '\\Sitegear\\View\\Decorator\\ExcerptDecorator',
			'resource-tokens' => '\\Sitegear\\View\\Decorator\\ResourceTokensDecorator',
			'string-tokens' => '\\Sitegear\\View\\Decorator\\StringTokensDecorator',
			'comments' => '\\Sitegear\\View\\Decorator\\CommentsDecorator',
			'sign-off' => '\\Sitegear\\View\\Decorator\\SignOffDecorator'
		),

		/**
		 * Page resource types to register.
		 */
		'resource-types' => array(
			'styles' => '<link '.'rel="stylesheet" type="text/css" href="%url%" />',
			'script' => '<script '.'src="%url%"></script>' // '.' = prevent warning: PhpStorm 5.0.4
		),

		/**
		 * Page-level (top-level) rendering settings.
		 */
		'page' => array(

			/**
			 * Document type settings.
			 */
			'doctype' => array(
				'xml-spec' => 'xml',
				'html-spec' => 'html',
				'display-xml-spec' => false,
				'display-doctype' => true
			),

			/**
			 * Default content type for page responses.  Null means use the Symfony components default, which is
			 * currently "text/html".  Possible meaningful values are "application/xhtml+xml" and "application/xml".
			 */
			'content-type' => null,

			/**
			 * First target name passed to the view's pushTarget() method at the page level.  The second target level
			 * is derived from the Request's "_target" attribute, which is in turn derived from the template map and
			 * other determinations.
			 */
			'template-module' => 'template',

			/**
			 * Decorator keys to apply to the page level.
			 */
			'decorators' => array(
				'resource-tokens',
				'string-tokens',
				'sign-off'
			)
		)
	),

	/**
	 * Resources to register.
	 */
	'resources' => '{{ include:$engine/config/resources.php }}',

	/**
	 * Module overrides.  Sub-keys should be module keys, and values should be config arrays.  The default for each
	 * module is given by that module's defaults() method.
	 */
	'modules' => array()

);
