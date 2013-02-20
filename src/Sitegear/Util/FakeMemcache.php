<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Util;

/**
 * A class that provides null implementations of all of the methods provided by the Memcache class.  An instance of
 * this class should be used in place of an actual Memcache object when the real implementation is not available or
 * not desired.
 */
class FakeMemcache extends \Memcache {

	public function connect($host, $port = null, $timeout = null) {
	}

	public function pconnect($host, $port = null, $timeout = null) {
	}

	public function addserver($host, $port = 11211, $persistent = null, $weight = null, $timeout = null, $retry_interval = null, $status = null, $failure_callback = null, $timeoutms = null) {
	}

	public function setserverparams() {
	}

	public function setfailurecallback() {
	}

	public function getserverstatus() {
		return null;
	}

	public function findserver() {
		return null;
	}

	public function getversion() {
		return null;
	}

	public function add($key, $var, $flag, $expire) {
	}

	public function set() {
	}

	public function replace() {
	}

	public function cas() {
	}

	public function append() {
	}

	public function prepend() {
	}

	public function get($key, &$flags = null) {
		return null;
	}

	public function delete() {
	}

	public function getstats() {
		return null;
	}

	public function getextendedstats() {
		return null;
	}

	public function setcompressthreshold() {
	}

	public function increment() {
	}

	public function decrement() {
	}

	public function close() {
	}

	public function flush() {
	}

}
