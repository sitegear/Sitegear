<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Ext\Module\SwiftMailer;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Util\ArrayUtilities;
use Sitegear\Util\TypeUtilities;
use Sitegear\Util\NameUtilities;
use Sitegear\Util\LoggerRegistry;

use Symfony\Component\HttpFoundation\Request;

/**
 * Send mails using the Swiftmailer library and Sitegear's template rendering engine.
 */
class SwiftMailerModule extends AbstractConfigurableModule {

	//-- Attributes --------------------

	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;

	//-- ModuleInterface Methods --------------------

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName() {
		return 'SwiftMailer Integration';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
		LoggerRegistry::debug('SwiftMailerModule starting');
		// The transport name cannot be converted due to old-school class names in SwiftMailer with underscores
		$transportName = $this->config('transport');
		$transportClass = new \ReflectionClass($transportName);
		/** @noinspection PhpParamsInspection The null gives a warning phpStorm 5.0.4 but is valid */
		$transport = $transportClass->getMethod('newInstance')->invokeArgs(null, $this->config('transportArguments', array()));
		$this->mailer = \Swift_Mailer::newInstance($transport);
	}

	//-- Public Methods --------------------

	/**
	 * Send an email with a static (supplied) subject and message body.
	 *
	 * @param string $subject Subject line for the message.
	 * @param array[] $addresses Map of recipients, with type keys (like "from", "to", "bcc", etc) mapped to email
	 *   address values.  Each address may be either a string or an array mapping email addresses to display names
	 *   and/or strings which are email addresses.
	 * @param string $body Body of the message.
	 * @param string|null $contentType
	 * @param string|null $charset
	 *
	 * @return boolean
	 *
	 * @throws \DomainException
	 * @throws \InvalidArgumentException
	 */
	public function send($subject, $addresses, $body, $contentType=null, $charset=null) {
		LoggerRegistry::debug(sprintf('SwiftMailerModule sending email with subject "%s" and body of length %d characters', $subject, strlen($body)));
		$message = \Swift_Message::newInstance($subject);
		if (!isset($addresses['sender']) && (!isset($addresses['from']) || !is_array($addresses['from']) || empty($addresses['from']))) {
			throw new \DomainException('SwiftMailer module cannot send without specifying a sender or from address.');
		}
		if (!isset($addresses['sender'])) {
			$addresses['sender'] = $addresses['from'];
		}
		foreach ($addresses as $type => $typeAddresses) {
			// Get the setter method, do it now so we don't determine arguments when the method doesn't even exist.
			$setter = new \ReflectionMethod($message, sprintf('set%s', NameUtilities::convertToStudlyCaps($type)));

			// Determine the arguments to the setter.  This converts an array of strings or maps, where each map has
			// "name" and "address" keys (name is optional), to a single array as accepted by the `setXxx()` methods.
			$setterAddressesArg = array();
			foreach ($typeAddresses as $typeAddress) {
				if (is_string($typeAddress)) {
					$setterAddressesArg[] = $typeAddress;
				} elseif (is_array($typeAddress) && isset($typeAddress['address'])) {
					if (isset($typeAddress['name'])) {
						$setterAddressesArg[$typeAddress['address']] = $typeAddress['name'];
					} else {
						$setterAddressesArg[] = $typeAddress['address'];
					}
				} else {
					throw new \InvalidArgumentException(sprintf('SwiftMailer module received invalid %s address specification: [%s]', $type, TypeUtilities::describe($typeAddress)));
				}
			}

			// Call the setter method.
			$setter->invoke($message, $setterAddressesArg);
		}
		$message->setBody($body, $contentType, $charset);
		return $this->mailer->send($message) > 0;
	}

	/**
	 * Send an email based on a template and supplied data.
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param string $subject Subject line for the message.
	 * @param array[] $addresses Map of recipients as required by the `send()` method.
	 * @param string $template Template name.
	 * @param array $data Data to use in the template (i.e. View data).
	 * @param string|null $contentType
	 * @param string|null $charset
	 *
	 * @return boolean
	 */
	public function sendTemplate(Request $request, $subject, $addresses, $template, $data, $contentType=null, $charset=null) {
		LoggerRegistry::debug(sprintf('SwiftMailerModule sending email from template %s', $template));
		$view = $this->getEngine()->getViewFactory()->buildView($request);
		// TODO Should there be a `mergeData()` method on ViewInterface to do this?  Is it being done elsewhere??
		foreach ($data as $key => $value) {
			$view[$key] = $value;
		}
		$body = $view->pushTarget('template')->pushTarget($template);
		return $this->send($subject, $addresses, $body, $contentType, $charset);
	}

	/**
	 * Send a simple plain-text notification email with the given data.  This is useful for contact form submissions as
	 * the notification sent to the site owner.
	 *
	 * @param string $subject
	 * @param array[] $addresses
	 * @param array $data
	 * @param string $intro Text to display above the data list
	 * @param string $outro Text to display below the data list
	 * @param string|null $charset
	 *
	 * @return boolean
	 */
	public function sendTextNotification($subject, $addresses, $data, $intro=null, $outro=null, $charset=null) {
		LoggerRegistry::debug('SwiftMailerModule sending text notification email.');
		$nl = "\r\n";
		$body = sprintf('** %s **', $subject) . $nl . $nl;
		$body .= ($intro ?: 'The following data was submitted:') . $nl . $nl;
		foreach ($data as $key => $value) {
			$body .= sprintf(' * %s: %s', NameUtilities::convertToTitleCase($key), $value) . $nl;
		}
		$body .= $nl . ($outro ?: 'This message text was automatically generated.') . $nl . $nl;
		return $this->send($subject, $addresses, $body, 'text/plain', $charset);
	}

}
