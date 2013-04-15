<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\View\Resources;

/**
 * Defines the behaviour of a registry for page resources, such as CSS and JavaScript, categorised by type.  Page
 * resources are specified as strings, and each type provides a format pattern that specifies the way that the resource
 * is rendered.
 *
 * This allows code throughout the page rendering process to easily specify resources that are required by that code,
 * without polluting the rendering process itself.
 *
 * Note that this interface intentionally allows only registration of resource types and resources, never removal.
 */
interface ResourcesManagerInterface {

	//-- Type Registration Methods --------------------

	/**
	 * Register a new resource type, which renders its resources using the given format specifier.
	 *
	 * @param string $type Name of the type.
	 * @param string $format Format to use when rendering the resources added for this resource type.
	 *
	 * @throws \LogicException If the given resource type is already registered.
	 * @throws \InvalidArgumentException If the given resource type is not of the correct format, i.e. alphanumeric
	 *   characters and hyphens only.
	 */
	public function registerType($type, $format);

	/**
	 * Register a type map, which is an associative array with type keys and corresponding format values.  This should
	 * be the equivalent to calling registerType on each of the key-value pairs.
	 *
	 * @param string[] $typeMap Associative array.
	 *
	 * @throws \LogicException If any of the given resource types are already registered.
	 * @throws \InvalidArgumentException If any of the given resource types are not of the correct format, i.e.
	 *   alphanumeric characters and hyphens only.
	 */
	public function registerTypeMap(array $typeMap);

	/**
	 * Determine whether the given resource type is registered.
	 *
	 * @param string $type Type to check.
	 *
	 * @return boolean Whether or not the given type is registered.
	 */
	public function isTypeRegistered($type);

	/**
	 * Get the names of all registered resource types.
	 *
	 * @return array List of names that are valid to pass to the other methods of this class.
	 */
	public function types();

	/**
	 * Retrieve the rendering format used by the specified resource type.
	 *
	 * @param string $type Resource type to find the format for.
	 *
	 * @return string Format used when rendering the specified resource type, or null if it is not registered.
	 */
	public function getFormat($type);

	//-- Resource Methods --------------------

	/**
	 * Add a resource of the given type, at the given URL.  Each URL can only be registered once for a given type, If
	 * the same URL is registered a second time, the second call will be ignored.
	 *
	 * @param string $key Unique key for the resource being added.
	 * @param string $type Resource type being added.
	 * @param string $url URL for the resource being added.
	 * @param boolean $active Whether or not to automatically mark the URL as active.
	 * @param string[]|string $requires Resource dependencies, or a single dependency.
	 *
	 * @throws \DomainException If the given resource type is not registered.
	 */
	public function register($key, $type, $url, $active=false, $requires=array());

	/**
	 * Register a map of resources.  The values are arrays which consist of 'type', 'url' and optional 'active' and
	 * 'requires' keys.  The 'requires' key should be mapped to an array of keys, which are the dependencies of this
	 * resource.
	 *
	 * @param array[] $map Map describing the resources to register.
	 *
	 * @throws \DomainException If any of the given resource types are not registered.
	 */
	public function registerMap(array $map);

	/**
	 * Determine whether the given key is registered.
	 *
	 * @param string $key Resource key to check for.
	 *
	 * @return boolean True if the given resource key has been added, otherwise false.
	 */
	public function isRegistered($key);

	/**
	 * Determine whether the given key has been both registered and activated.
	 *
	 * @param string $key Resource key to check for.
	 *
	 * @return boolean True if the given resource key has been added and is currently active, otherwise false.
	 */
	public function isActive($key);

	/**
	 * Get the resource type for the specified resource key.
	 *
	 * @param string $key Resource to check.
	 *
	 * @return string Type name, or null if the resource is not registered.
	 */
	public function getType($key);

	/**
	 * Get the registered URL for the specified resource key.
	 *
	 * @param string $key Resource to check.
	 *
	 * @return string URL, or null if the resource is not registered.
	 */
	public function getUrl($key);

	/**
	 * Retrieve the array of URLs added for the specified resource type.  By default this includes only the activated
	 * resources, but by passing true as the second argument, all URLs are returned.
	 *
	 * @param string $type Resource type to find the URLs for.  Use null (the default) to include all types.
	 * @param boolean $includeInactive
	 *
	 * @return array Array of URLs for the given resource type, or all types.
	 *
	 * @throws \DomainException If the given resource type is not registered.
	 */
	public function getAllUrls($type, $includeInactive=false);

	//-- Rendering Methods --------------------

	/**
	 * Mark the given resource as required, which means it will be rendered.  If the key is not known, this call should
	 * be ignored.
	 *
	 * @param string $key Resource key to mark required.
	 */
	public function activate($key);

	/**
	 * Render all added resources of the specified type, and return the result.
	 *
	 * @param string $type Resource type to render.
	 *
	 * @return string Rendered content.  By default this is HTML, but this can be anything according to the formats
	 *   associated with the various resource types.
	 *
	 * @throws \DomainException If the given resource type is not registered.
	 */
	public function render($type);

	//-- Utility Methods --------------------

	/**
	 * Determine if the given resource type specifier is valid.  A valid specifier is a non-empty string consisting of
	 * only upper and lower case letters, numerals, and hyphens.
	 *
	 * @param string $type Type specifier to check.
	 *
	 * @return boolean True if the resource type is valid, otherwise false.
	 */
	public function isValidType($type);

}
