<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\PageMessages;

use Sitegear\Base\View\ViewInterface;
use Sitegear\Core\Module\AbstractCoreModule;

/**
 * Provides a simple means of storing, retrieving and rendering messages that can be set by any other module and are
 * displayed "globally" at the same place within all pages (i.e. "flash messages").
 */
class PageMessagesModule extends AbstractCoreModule {

	//-- Attributes --------------------

	/**
	 * @var array[] Cached messages.
	 */
	private $messages;

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'Page Messages';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		$this->messages = $this->getEngine()->getSession()->get($this->config('session-key'));
	}

	//-- Component Controller Methods --------------------

	/**
	 * Component to display the page messages.
	 *
	 * @param ViewInterface $view
	 */
	public function messagesComponent(ViewInterface $view) {
		$this->applyConfigToView('components.messages', $view);
		$view['messages'] = $this->retrieve();
	}

	//-- Public Methods --------------------

	/**
	 * Add the given message.
	 *
	 * @param string $message
	 * @param string $class Class attribute value for the message wrapper element.
	 */
	public function add($message, $class=null) {
		$this->messages[] = array(
			'message' => $message,
			'class' => $class
		);
		$this->getEngine()->getSession()->set($this->config('session-key'), $this->messages);
	}

	/**
	 * Retrieve the list of messages.
	 *
	 * @param boolean $clear Whether or not to also call clear() prior to returning the retrieved messages.  Is set to
	 *   true by default so that messages will normally only ever be displayed once.
	 *
	 * @return string[]
	 */
	public function retrieve($clear=true) {
		$result = $this->messages;
		if ($clear) {
			$this->clear();
		}
		return $result;
	}

	/**
	 * Remove all messages.
	 */
	public function clear() {
		$this->messages = array();
		$this->getEngine()->getSession()->remove($this->config('session-key'));
	}

}
