<?php
/**
 * Wallee SDK
 *
 * This library allows to interact with the Wallee payment service.
 * Wallee SDK: 1.0.0
 * 
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Wallee\Sdk\Model;



/**
 * CriteriaOperator model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      customweb GmbH
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 * @link        https://github.com/wallee-payment/wallee-php-sdk
 */
class CriteriaOperator implements IEnum {

	const EQUALS = 'EQUALS';
	const GREATER_THAN = 'GREATER_THAN';
	const GREATER_THAN_OR_EQUAL = 'GREATER_THAN_OR_EQUAL';
	const LESS_THAN = 'LESS_THAN';
	const LESS_THAN_OR_EQUAL = 'LESS_THAN_OR_EQUAL';
	const CONTAINS = 'CONTAINS';
	const NOT_EQUALS = 'NOT_EQUALS';
	const NOT_CONTAINS = 'NOT_CONTAINS';
	const IS_NULL = 'IS_NULL';
	

	

}
