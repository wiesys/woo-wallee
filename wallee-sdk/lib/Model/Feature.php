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

use Wallee\Sdk\ValidationException;

/**
 * Feature model
 *
 * @category    Class
 * @description 
 * @package     Wallee\Sdk
 * @author      customweb GmbH
 * @license     http://www.apache.org/licenses/LICENSE-2.0 Apache License v2
 * @link        https://github.com/wallee-payment/wallee-php-sdk
 */
class Feature  {

	/**
	 * The original name of the model.
	 *
	 * @var string
	 */
	private static $swaggerModelName = 'Feature';

	/**
	 * An array of property to type mappings. Used for (de)serialization.
	 *
	 * @var string[]
	 */
	private static $swaggerTypes = array(
		'beta' => 'bool',
		'description' => 'map[string,string]',
		'id' => 'int',
		'logoPath' => 'string',
		'name' => 'map[string,string]',
		'requiredFeatures' => 'int[]',
		'sortOrder' => 'int',
		'visible' => 'bool'	);

	/**
	 * Returns an array of property to type mappings.
	 *
	 * @return string[]
	 */
	public static function swaggerTypes() {
		return self::$swaggerTypes;
	}

	

	/**
	 * 
	 *
	 * @var bool
	 */
	private $beta;

	/**
	 * 
	 *
	 * @var map[string,string]
	 */
	private $description;

	/**
	 * The ID is the primary key of the entity. The ID identifies the entity uniquely.
	 *
	 * @var int
	 */
	private $id;

	/**
	 * 
	 *
	 * @var string
	 */
	private $logoPath;

	/**
	 * 
	 *
	 * @var map[string,string]
	 */
	private $name;

	/**
	 * 
	 *
	 * @var int[]
	 */
	private $requiredFeatures;

	/**
	 * 
	 *
	 * @var int
	 */
	private $sortOrder;

	/**
	 * 
	 *
	 * @var bool
	 */
	private $visible;


	/**
	 * Constructor.
	 *
	 * @param mixed[] $data an associated array of property values initializing the model
	 */
	public function __construct(array $data = null) {
		if (isset($data['description'])) {
			$this->setDescription($data['description']);
		}
		if (isset($data['name'])) {
			$this->setName($data['name']);
		}
		if (isset($data['requiredFeatures'])) {
			$this->setRequiredFeatures($data['requiredFeatures']);
		}
	}


	/**
	 * Returns beta.
	 *
	 * 
	 *
	 * @return bool
	 */
	public function getBeta() {
		return $this->beta;
	}

	/**
	 * Sets beta.
	 *
	 * @param bool $beta
	 * @return Feature
	 */
	protected function setBeta($beta) {
		$this->beta = $beta;

		return $this;
	}

	/**
	 * Returns description.
	 *
	 * 
	 *
	 * @return map[string,string]
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets description.
	 *
	 * @param map[string,string] $description
	 * @return Feature
	 */
	public function setDescription($description) {
		$this->description = $description;

		return $this;
	}

	/**
	 * Returns id.
	 *
	 * The ID is the primary key of the entity. The ID identifies the entity uniquely.
	 *
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Sets id.
	 *
	 * @param int $id
	 * @return Feature
	 */
	protected function setId($id) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Returns logoPath.
	 *
	 * 
	 *
	 * @return string
	 */
	public function getLogoPath() {
		return $this->logoPath;
	}

	/**
	 * Sets logoPath.
	 *
	 * @param string $logoPath
	 * @return Feature
	 */
	protected function setLogoPath($logoPath) {
		$this->logoPath = $logoPath;

		return $this;
	}

	/**
	 * Returns name.
	 *
	 * 
	 *
	 * @return map[string,string]
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets name.
	 *
	 * @param map[string,string] $name
	 * @return Feature
	 */
	public function setName($name) {
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns requiredFeatures.
	 *
	 * 
	 *
	 * @return int[]
	 */
	public function getRequiredFeatures() {
		return $this->requiredFeatures;
	}

	/**
	 * Sets requiredFeatures.
	 *
	 * @param int[] $requiredFeatures
	 * @return Feature
	 */
	public function setRequiredFeatures($requiredFeatures) {
		$this->requiredFeatures = $requiredFeatures;

		return $this;
	}

	/**
	 * Returns sortOrder.
	 *
	 * 
	 *
	 * @return int
	 */
	public function getSortOrder() {
		return $this->sortOrder;
	}

	/**
	 * Sets sortOrder.
	 *
	 * @param int $sortOrder
	 * @return Feature
	 */
	protected function setSortOrder($sortOrder) {
		$this->sortOrder = $sortOrder;

		return $this;
	}

	/**
	 * Returns visible.
	 *
	 * 
	 *
	 * @return bool
	 */
	public function getVisible() {
		return $this->visible;
	}

	/**
	 * Sets visible.
	 *
	 * @param bool $visible
	 * @return Feature
	 */
	protected function setVisible($visible) {
		$this->visible = $visible;

		return $this;
	}

	/**
	 * Validates the model's properties and throws a ValidationException if the validation fails.
	 *
	 * @throws ValidationException
	 */
	public function validate() {

	}

	/**
	 * Returns true if all the properties in the model are valid.
	 *
	 * @return boolean
	 */
	public function isValid() {
		try {
			$this->validate();
			return true;
		} catch (ValidationException $e) {
			return false;
		}
	}

	/**
	 * Returns the string presentation of the object.
	 *
	 * @return string
	 */
	public function __toString() {
		if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
			return json_encode(\Wallee\Sdk\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
		}

		return json_encode(\Wallee\Sdk\ObjectSerializer::sanitizeForSerialization($this));
	}

}

