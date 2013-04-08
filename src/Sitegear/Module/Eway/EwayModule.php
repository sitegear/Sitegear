<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Module\Eway;

use Sitegear\Base\Module\PaymentGatewayModuleInterface;
use Sitegear\Core\Module\AbstractCoreModule;

/**
 * Eway integration module.
 */
class EwayModule extends AbstractCoreModule implements PaymentGatewayModuleInterface {

	//-- ModuleInterface Methods --------------------

	/**
	 * @inheritdoc
	 */
	public function getDisplayName() {
		return 'eWAY Payment Gateway';
	}

	//-- PaymentGatewayModuleInterface --------------------

	/**
	 * @inheritdoc
	 */
	public function createPaymentToken(array $data) {
		// TODO: Implement createPaymentToken() method.
		return 'token123';
	}

	/**
	 * @inheritdoc
	 */
	public function completePayment($token) {
		// TODO: Implement completePayment() method.
	}

}
