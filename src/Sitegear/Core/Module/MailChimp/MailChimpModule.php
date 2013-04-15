<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Core\Module\MailChimp;

use Rezzza\MailChimp\MCAPI;
use Sitegear\Core\Module\AbstractCoreModule;

/**
 * MailChimp integration module.
 */
class MailChimpModule extends AbstractCoreModule {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'MailChimp Integration';
	}

	//-- Public Methods --------------------

	/**
	 * @param string $apiKey
	 * @param string $listId
	 * @param array $data
	 * @param array[] $fieldMap
	 * @param array $settings
	 * @param string $emailFieldName
	 *
	 * @throws \InvalidArgumentException If the field map is invalid.
	 * @throws \RuntimeException If the API returns an error result.
	 */
	public function subscribe($apiKey, $listId, array $data, array $fieldMap, array $settings=array(), $emailFieldName='email') {
		// Connect to the MailChimp API.
		$api = new MCAPI($apiKey ?: $this->config('api.key'));
		$listId = $listId ?: $this->config('api.list-id');

		// Get settings to pass to listSubscribe API call.
		$emailType = isset($settings['email-type']) ? $settings['email-type'] : $this->config('mcapi.defaults.list-subscribe.email-type');
		$doubleOptin = isset($settings['double-optin']) ? $settings['double-optin'] : $this->config('mcapi.defaults.list-subscribe.double-optin');
		$updateExisting = isset($settings['update-existing']) ? $settings['update-existing'] : $this->config('mcapi.defaults.list-subscribe.update-existing');
		$replaceInterests = isset($settings['replace-interests']) ? $settings['replace-interests'] : $this->config('mcapi.defaults.list-subscribe.replace-interests');
		$sendWelcome = isset($settings['send-welcome']) ? $settings['send-welcome'] : $this->config('mcapi.defaults.list-subscribe.send-welcome');

		// Get data for MCAPI based on passed-in settings and passed-in data.
		$mailChimpData = array();
		foreach ($fieldMap as $field) {
			if (!isset($field['merge-field']) || (!isset($field['form-field']) && !isset($field['value']))) {
				throw new \InvalidArgumentException("MailChimpModule found invalid field mapping without both a 'merge-field` and either a 'form-field' or a fixed 'value'.");
			}
			// Look for an override; if there is no override, default to either the value from the data array or a
			// fixed value.
			$mailChimpData[$field['merge-field']] = $this->config(
				sprintf('merge-field-overrides.%s', $field['merge-field']),
				isset($field['form-field']) ? $data[$field['form-field']] : $field['value']
			);
		}

		// Check if the user is already subscribed, if so it is an update otherwise it is a subscribe,
		$info = $api->listMemberInfo($listId, array( $data[$emailFieldName] ));
		$result = (empty($info['data']) || $info['data'][0]['status'] === 'unsubscribed') ?
				$api->listSubscribe($listId, $data[$emailFieldName], $mailChimpData, $emailType, $doubleOptin, $updateExisting, $replaceInterests, $sendWelcome) :
				$api->listUpdateMember($listId, $data[$emailFieldName], $mailChimpData, $emailType, $replaceInterests);

		// Check for errors.
		if (!$result) {
			throw new \RuntimeException(sprintf('MailChimpModule encountered error during subscribe(): "%s" (code: %d)', $api->errorMessage, $api->errorCode), $api->errorCode);
		}
	}

}
