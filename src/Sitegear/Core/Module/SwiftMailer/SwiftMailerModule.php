<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\SwiftMailer;

use Sitegear\Base\Module\AbstractConfigurableModule;
use Sitegear\Util\NameUtilities;

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
		return 'Mail Processor';
	}

	/**
	 * {@inheritDoc}
	 */
	public function start() {
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
	 * @param string $body Body of the message.
	 * @param array[] $addresses Map of recipients, with type keys (like "from", "to", "bcc", etc) mapped to email
	 *   address values.  Each address may be either a string or an array mapping email addresses to display names
	 *   and/or strings which are email addresses.
	 *
	 * @return int Number of successful recipients.
	 */
	public function send($subject, $body, $addresses) {
		$message = \Swift_Message::newInstance($subject);
		foreach ($addresses as $type => $typeAddresses) {
			$setter = new \ReflectionMethod($message, sprintf('set%s', NameUtilities::convertToStudlyCaps($type)));
			return $setter->invoke($message, $typeAddresses);
		}
		$message->setBody($body); // TODO MIME type?
		return $this->mailer->send($message);
	}

	/**
	 * Send an email based on a template and supplied data.
	 *
	 * @param string $template Template name.
	 * @param array $data Data to use in the template (i.e. View data).
	 * @param string $subject Subject line for the message.
	 * @param array[] $addresses Map of recipients as required by the `send()` method.
	 *
	 * @return int Number of successful recipients.
	 */
	public function sendTemplate($template, $data, $subject, $addresses) {
		$body = '<h1>Test Message</h1><p>Initial test of SwiftMailer...</p>'; // TODO Generate proper templated body
		return $this->send($subject, $body, $addresses);
	}

}
