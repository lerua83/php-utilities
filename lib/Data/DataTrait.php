<?php
declare(strict_types=1);
/*
 * This file is part of the php-utilities package.
 *
 * (c) Marc Aschmann <maschmann@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Asm\Data;

use Asm\Exception\InvalidParameterException;
use Asm\Exception\InvalidParameterSetException;

/**
 * DataTrait for easier inclusion in other objects without direct inheritance
 *
 * @package Asm\Data
 * @author Marc Aschmann <maschmann@gmail.com>
 */
trait DataTrait
{
    /**
     * Internal data storage.
     *
     * @var array
     */
    private $data = [];

    /**
     * Clears the data(!) content of the object.
     *
     * @return $this
     */
    public function clear()
    {
        $this->data = [];

        return $this;
    }

    /**
     * Generic set method for multidimensional storage.
     *
     * $this->set( $key1, $key2, $key3, ..., $val )
     *
     * @throws InvalidParameterSetException
     * @return $this
     */
    public function set()
    {
        $args = func_get_args();
        $replace = null;

        if (1 < count($args)) {
            // get last array element == value to set!
            $val = array_pop($args);

            // iterate arguments reversed to build replacement array
            foreach (array_reverse($args) as $key) {
                if (null === $replace) {
                    $replace = [$key => $val];
                } else {
                    $replace = [$key => $replace];
                }
            }

            // add our data to storage
            $this->data = array_replace_recursive($this->data, $replace);
        } else {
            throw new InvalidParameterSetException(
                "Data::set() - You just provided one param and need at least another param."
            );
        }

        return $this;
    }

    /**
     * Set list of key/value pairs via one dimensional array.
     * Careful: An empty array will just overwrite your internal storage.
     *
     * @param  array $param
     * @return $this
     */
    public function setByArray(array $param)
    {
        if (!empty($param)) {
            // reset array to explicitly start at beginning
            reset($param);
            foreach ($param as $key => $value) {
                $this->set($key, $value);
            }
        } else { // will overwrite content with an empty array
            $this->data = $param;
        }

        return $this;
    }

    /**
     * Adds given object's properties to self.
     *
     * @param  object $param
     * @return $this
     * @throws InvalidParameterException
     */
    public function setByObject($param)
    {
        // check for DataContainer instances - because otherwise you can't easily access virtual properties
        if (is_object($param)) {
            if (is_a($param, DataInterface::class, true)) {
                foreach ($param->toArray() as $key => $value) {
                    $this->set($key, $value);
                }
            } else {
                // handle as "normal" object
                foreach ($param as $property => $value) {
                    $this->set($property, $value);
                }
            }
        } else {
            throw new InvalidParameterException(
                "Data::setByObject() - param is a {gettype($param)},no object!"
            );
        }

        return $this;
    }

    /**
     * Fill datastore from json string.
     *
     * @param string $json
     * @return $this
     */
    public function setByJson(string $json)
    {
        $this->setByArray(
            json_decode($json, true)
        );

        return $this;
    }

    /**
     * Return stored data array.
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->data;
    }

    /**
     * Convert internal data to json.
     *
     * @return string
     */
    public function toJson() : string
    {
        return json_encode($this->data);
    }

    /**
     * Multidimensional getter.
     *
     * Find a key structure in a multidimensional array and return the value
     * params are stackable -> get( $k1, $k2, $k3, ... ).
     *
     * @return bool|mixed
     */
    public function get()
    {
        return self::searchArray(
            func_get_args(),
            $this->data
        );
    }

    /**
     * Return all keys of internal array's first level.
     *
     * @return array keylist
     */
    public function getKeys() : array
    {
        return array_keys($this->data);
    }

    /**
     * Remove key from container.
     *
     * @param  string $key
     * @return $this
     */
    public function remove(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Return count of all firstlevel elements.
     *
     * @return int
     */
    public function count() : int
    {
        return count($this->data);
    }

    /**
     * Find a key in an array.
     * example self::findInArray(array(), key1, key2, key3, ..., default_return)
     *
     * @return array|bool|mixed
     */
    public static function findInArray()
    {
        $args = func_get_args();
        $data = array_shift($args);

        return self::searchArray(
            $args,
            $data
        );
    }

    /**
     * Search an array for keys (args) and provide a default value if
     * last arg is some kind of empty or not numeric.
     *
     * @param array $args
     * @param array $data
     * @param bool $default
     * @return array|mixed
     */
    private static function searchArray(array $args, array $data, $default = false)
    {
        // check for default return value
        if (1 < count($args)) {
            $lastElm = array_pop($args);
            if (empty($lastElm) && !is_numeric($lastElm)) {
                $default = $lastElm;
            } else {
                // push the last element back into array
                array_push($args, $lastElm);
            }
        }

        foreach ($args as $key) {
            if ((is_array($data)) && (array_key_exists($key, $data))) {
                $data = $data[$key];
            } else {
                $data = $default;
                break;
            }
        }

        return $data;
    }
}
