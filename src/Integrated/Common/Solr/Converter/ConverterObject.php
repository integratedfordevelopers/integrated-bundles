<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Solr\Converter;

use ArrayAccess;
use ReflectionClass;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class ConverterObject implements ArrayAccess
{
	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	public function value()
	{
		if (is_scalar($this->value)) {
			return $this->value;
		}

		return null;
	}

	public function raw()
	{
		return $this->value;
	}

	public function isEmpty()
	{
		return empty($this->value);
	}

	public function isValue()
	{
		return is_scalar($this->value);
	}

	public function isArray()
	{
		return is_array($this->value) || $this->value instanceof ArrayAccess;
	}

	public function isScalar()
	{
		return is_scalar($this->value);
	}

	public function isObject()
	{
		return is_object($this->value);
	}

	public function offsetExists($offset)
	{
		if ($this->isArray()) {
			return isset($this->value[$offset]);
		}

		return false;
	}

	public function offsetGet($offset)
	{
		if ($this->offsetExists($offset)) {
			return new self(null);
		}

		return new self($this->value[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		// not supported.
	}

	public function offsetUnset($offset)
	{
		// not supported.
	}

	public function __isset($name)
	{
		if ($this->isScalar()) {
			return false;
		}

		$value = $this->value;

		// if the $value is a array get the first entry in that array this
		// will be done only ones as if its a array in a array then the config
		// should reflect that.

		if ($this->isArray()) {
			$value = isset($value[0]) ? $value[0] : null;
		}

		// So it has to be a object or else its not possible to call a property
		// in the first place

		if (is_object($value)) {
			$reflection = new ReflectionClass($value);

			if ($reflection->hasProperty($name) && ($prop = $reflection->getProperty($name)) && $prop->isPublic()) {
				return $prop->getValue($value) !== null;
			}

			$method = 'get' . ucfirst($name);

			if ($reflection->hasMethod($method) && ($method = $reflection->getMethod($method)) && $method->isPublic() && $method->getNumberOfRequiredParameters() == 0) {
				return $method->invoke($value) !== null;
			}

			// @todo check for __get, __isset or __call
		}

		return false;
	}

	public function __get($name)
	{
		if ($this->isScalar()) {
			return new self(null);
		}

		$value = $this->value;

		// if the $value is a array get the first entry in that array this
		// will be done only ones as if its a array in a array then the config
		// should reflect that.

		if ($this->isArray()) {
			$value = isset($value[0]) ? $value[0] : null;
		}

		// So it has to be a object or else its not possible to call a property
		// in the first place

		if (is_object($value)) {
			$reflection = new ReflectionClass($value);

			if ($reflection->hasProperty($name) && ($prop = $reflection->getProperty($name)) && $prop->isPublic()) {
				return new self($prop->getValue($value));
			}

			$method = 'get' . ucfirst($name);

			if ($reflection->hasMethod($method) && ($method = $reflection->getMethod($method)) && $method->isPublic() && $method->getNumberOfRequiredParameters() == 0) {
				return new self($method->invoke($value));
			}

			// @todo check for __get, __isset or __call
		}

		return new self(null);
	}

	public function __set($name, $value)
	{
		// not supported.
	}

	public function __unset($name)
	{
		// not supported.
	}
}