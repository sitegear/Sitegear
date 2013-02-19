<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * A class that provides null implementations of all of the methods provided by the Memcached class.  An instance of
 * this class should be used in place of an actual Memcached object when the real implementation is not available or
 * not desired.
 */
class FakeMemcached {

	public function getResultCode() {
		return null;
	}

	public function getResultMessage() {
		return null;
	}

	public function get($key, $cache_cb = null, &$cas_token = null) {
		return null;
	}

	public function getByKey($server_key, $key, $cache_cb = null, &$cas_token = null) {
		return null;
	}

	public function getMulti(array $keys, array &$cas_tokens = null, $flags = null) {
		return null;
	}

	public function getMultiByKey($server_key, array $keys, &$cas_tokens = null, $flags = null) {
		return null;
	}

	public function getDelayed(array $keys, $with_cas = null, $value_cb = null) {
		return null;
	}

	public function getDelayedByKey($server_key, array $keys, $with_cas = null, $value_cb = null) {
		return null;
	}

	public function fetch() {
		return null;
	}

	public function fetchAll() {
		return null;
	}

	public function set($key, $value, $expiration = null) {
	}

	public function setByKey($server_key, $key, $value, $expiration = null) {
	}

	public function setMulti(array $items, $expiration = null) {
	}

	public function setMultiByKey($server_key, array $items, $expiration = null) {
	}

	public function cas($cas_token, $key, $value, $expiration = null) {
	}

	public function casByKey($cas_token, $server_key, $key, $value, $expiration = null) {
	}

	public function add($key, $value, $expiration = null) {
	}

	public function addByKey($server_key, $key, $value, $expiration = null) {
	}

	public function append($key, $value) {
	}

	public function appendByKey($server_key, $key, $value) {
	}

	public function prepend($key, $value) {
	}

	public function prependByKey($server_key, $key, $value) {
	}

	public function replace($key, $value, $expiration = null) {
	}

	public function replaceByKey($server_key, $key, $value, $expiration = null) {
	}

	public function delete($key, $time = 0) {
	}

	public function deleteByKey($server_key, $key, $time = 0) {
	}

	public function increment($key, $offset = 1) {
	}

	public function decrement($key, $offset = 1) {
	}

	public function addServer($host, $port, $weight = 0) {
	}

	public function addServers(array $servers) {
	}

	public function getServerList() {
		return array();
	}

	public function getServerByKey($server_key) {
		return null;
	}

	public function getStats() {
		return array();
	}

	public function getVersion() {
		return null;
	}

	public function flush($delay = 0) {
	}

	public function getOption($option) {
		return null;
	}

	public function setOption($option, $value) {
	}

}
