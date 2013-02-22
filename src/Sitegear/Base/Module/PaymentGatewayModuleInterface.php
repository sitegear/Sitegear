<?php
/*!
 * This file is a part of Sitegear.
 * Copyright (c) Ben New, Leftclick.com.au
 * See the LICENSE and README files in the main source directory for details.
 * http://sitegear.org/
 */

namespace Sitegear\Base\Module;

/**
 * Describes the behaviour of a module which provides an integration with a payment gateway.  This interface describes
 * only the act of communicating with the payment gateway itself, using provided data and purchase details.
 */
interface PaymentGatewayModuleInterface extends ModuleInterface {

	/**
	 * Create a payment token using the specified data.
	 *
	 * @param array $data Key-value array containing merge field name keys mapped to values.
	 *
	 * @return string Token to pass to completePayment().
	 *
	 * @throws \RuntimeException
	 */
	public function createPaymentToken(array $data);

	/**
	 * Process the payment represented by the given token, which was previously setup using `createPayment()`.
	 *
	 * @param string $token Payment token to process.
	 *
	 * @throws \RuntimeException
	 */
	public function completePayment($token);

}
