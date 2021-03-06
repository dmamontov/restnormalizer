<?php
/**
 * RestNormalizer
 *
 * Copyright (c) 2015, Dmitry Mamontov <d.slonyara@gmail.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Dmitry Mamontov nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   restnormalizer
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.2
 */

 /**
 * RestNormalizer - The main class
 *
 * @author    Dmitry Mamontov <d.slonyara@gmail.com>
 * @copyright 2015 Dmitry Mamontov <d.slonyara@gmail.com>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: 1.0.2
 * @link      https://github.com/dmamontov/restnormalizer/
 * @since     Class available since Release 1.0.2
 */

class RestNormalizer
{
    /**
     * Encoding output values
     * @var string
     * @access protected
     */
    protected $encoding = 'UTF-8';

    /**
     * Cleanup of null values
     * @var boolean
     * @access protected
     */
    protected $clear = true;

    /**
     * The path to the file for logging
     * @var string
     * @access public
     */
    protected $logFile = '/tmp/normolizer-error.log';

    /**
     * Text format for logging
     * @var string
     * @access private
     */
    private $formatMessage = '';

    /**
     * Sorted file validation
     * @var array
     * @access private
     */
    private $validation = array();

    /**
     * File validation
     * @var array
     * @access private
     */
    private $originalValidation = array();

    /**
     * Class constructor
     * @return void
     * @access public
     * @final
     */
    final public function __construct()
    {
        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }

        $this->formatMessage = sprintf('[%s]', date('Y-m-d H:i:s'));
    }

    /**
     * Installation file validation
     * @param string $file The path to the file validation
     * @return RestNormalizer
     * @access public
     * @final
     */
    final public function setValidation($file)
    {
        if (is_null($file) || is_file($file) === false
            || json_decode(file_get_contents($file)) === null
            || $this->parseConfig($file) === false) {
            throw new RuntimeException('Incorrect file validation.');
        }

        return $this;
    }

    /**
     * Parsing the file validation
     * @param string $file The path to the file validation
     * @return boolean
     * @access private
     * @final
     */
    final private function parseConfig($file)
    {
        if (json_decode(file_get_contents($file)) !== null) {
            $this->originalValidation = json_decode(file_get_contents($file), true);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Starting the process of normalization of the data
     * @param array $data The key is to sort the data validation
     * @param string $key Data normalization
     * @return array
     * @access public
     * @final
     */
    final public function normalize($data, $key = false)
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (is_string($key) && isset($this->originalValidation[ $key ])) {
            $this->validation = $this->originalValidation[ $key ];
        } else {
            $this->validation = $this->originalValidation;
        }

        if (!is_array($data) || count($data) < 1) {
            throw new RuntimeException('Incorrect data array.');
        }

        $formatting = $this->formatting($data);
        $post = $this->onPostNormalize($formatting);

        return is_null($post) ? $formatting : $post;
    }

    /**
     * Data formatting
     * @param array $data The key is to sort the data validation
     * @param boolean $skip Skip perform methods intended for the first run
     * @return array
     * @access private
     * @final
     */
    final private function formatting($data, $skip = false)
    {
        $formatted = array();

        if ($skip === false) {
            $tmpData = $this->onPreNormalize($data);
            if (is_null($tmpData) === false) {
                $data = $tmpData;
            }
        }

        foreach ($data as $code => $value) {
            if (isset($this->validation[ $code ]) && $this->validation[ $code ]['type'] == 'skip') {
                $formatted[ $code ] = $value;
                continue;
            } elseif (isset($this->validation[ $code ]) && is_array($value) === false) {
                $formatted[ $code ] = $this->setFormat($value, $this->validation[ $code ]);
            } elseif (is_array($value)) {
                $formatted[ $code ] = $this->formatting($value, true);
            }

            if (is_null($formatted[ $code ]) || $formatted[ $code ] === '' || (is_array($formatted[ $code ]) && count($formatted[ $code ]) < 1)) {
                if ($this->clear === true) {
                    unset($formatted[ $code ]);
                }

                if (isset($this->validation[ $code ]['required']) && $this->validation[ $code ]['required'] === true) {
                    $formatted = array();
                    break;
                }
            }
        }

        if ($skip === false) {
            foreach ($this->validation as $code => $valid) {
                if (isset($valid['required']) && $valid['required'] === true && isset($formatted[ $code ]) === false) {
                    if ($this->logFile !== null) {
                        error_log(
                            sprintf("%s: NOT VALID(%s)\n", $this->formatMessage, json_encode($formatted)),
                            3,
                            $this->logFile
                        );
                    } else {
                        echo sprintf("%s: NOT VALID(%s)\n", $this->formatMessage, json_encode($formatted));
                    }
                }
            }

            $formatted = $this->multiIconv($formatted);
        }

        return count($formatted) < 1 ? false : $formatted;
    }

    /**
     * Formatting data depending on the type
     * @param mixed $data The value to be formatted
     * @param array $validation The data for the current data type validation
     * @return mixed
     * @access private
     * @final
     */
    final private function setFormat($data, $validation)
    {
        $format = null;

        switch ($validation['type']) {
            case 'string':
                $format = $this->setString($data, $validation);
                break;
            case 'int':
                $format = $this->setInt($data, $validation);
                break;
            case 'double':
                $format = $this->setDouble($data, $validation);
                break;
            case 'bool':
                $format = $this->setBool($data, $validation);
                break;
            case 'datetime':
                $format = $this->setDateTime($data, $validation);
                break;
            case 'enum':
                $format = $this->setEnum($data, $validation);
                break;
        }

        return $format;
    }

    /**
     * Formatting data for strings
     * @param string $data String to formatting
     * @param array $validation The data for the current data type validation
     * @return string
     * @access private
     * @final
     */
    final private function setString($data, $validation)
    {
        $data = trim((string) $data);

        if (isset($validation['default']) && is_string($validation['default']) && trim($validation['default']) != ''
            && ($data == '' || is_string($data) === false)) {
            $data = trim($validation['default']);
        } elseif ($data == '' || is_string($data) === false) {
            return null;
        } elseif (isset($validation['min']) && mb_strlen($data) < $validation['min']) {
            $pad = isset($validation['pad']) && mb_strlen($validation['pad']) == 1 ? $validation['pad'] : ' ';
            $data .= str_repeat($pad, $validation['min'] - mb_strlen($data));
        } elseif (isset($validation['max']) && mb_strlen($data) > $validation['max']) {
            $data = mb_substr($data, 0, $validation['max']);
        }

        return (string) $data;
    }

    /**
     * Formatting data for integers
     * @param integer $data Integer to formatting
     * @param array $validation The data for the current data type validation
     * @return integer
     * @access private
     * @final
     */
    final private function setInt($data, $validation)
    {
        if (isset($validation['default']) && is_numeric($validation['default']) && is_numeric($data) === false) {
            $data = $validation['default'];
        } elseif (is_numeric($data) === false) {
            return null;
        } elseif (isset($validation['min']) && $data < $validation['min']) {
            $data += $validation['min'] - $data;
        } elseif (isset($validation['max']) && $data > $validation['max']) {
            $data -= $data - $validation['max'];
        }

        return (int) $data;
    }

    /**
     * Formatting data for floating-point numbers
     * @param float $data Floating-point number to formatting
     * @param array $validation The data for the current data type validation
     * @return float
     * @access private
     * @final
     */
    final private function setDouble($data, $validation)
    {
        if (isset($validation['default']) && is_numeric($validation['default']) && is_numeric($data) === false) {
            $data = $validation['default'];
        } elseif (is_numeric($data) === false) {
            return null;
        } elseif (isset($validation['min']) && $data < $validation['min']) {
            $data += $validation['min'] - $data;
        } elseif (isset($validation['max']) && $data > $validation['max']) {
            $data -= $data - $validation['max'];
        }

        if (isset($validation['decimals'])) {
            $data = number_format($data, $validation['decimals']);
        }

        return (double) $data;
    }

    /**
     * Formatting data for logical values
     * @param boolean $data Boolean value to formatting
     * @param array $validation The data for the current data type validation
     * @return boolean
     * @access private
     * @final
     */
    final private function setBool($data, $validation)
    {
        if (isset($validation['default']) && is_bool($validation['default']) && is_bool($data) === false) {
            $data = $validation['default'];
        } elseif (is_bool($data) === false) {
            return null;
        }

        return (bool) $data;
    }

    /**
     * Formatting data for date and time
     * @param mixed $data Date and time of to formatting
     * @param array $validation The data for the current data type validation
     * @param boolean $skip Skip perform methods intended for the first run
     * @return mixed
     * @access private
     * @final
     */
    final private function setDateTime($data, $validation, $skip = false)
    {
        if (is_a($data, 'DateTime') && isset($validation['format'])) {
            $data = (string) $data->format($validation['format']);
        } elseif (is_string($data) && isset($validation['format']) && strtotime($data) !== false) {
            $data = (string) date($validation['format'], strtotime($data));
        } elseif (is_numeric($data) && isset($validation['format'])) {
            $data = (string) date($validation['format'], (int) $data);
        } elseif (is_numeric($data)) {
            $data = (int) $data;
        } elseif (isset($validation['format'])) {
            $data = (string) date($validation['format']);
        } elseif (isset($validation['default']) && $skip === false) {
            $data = $this->setDateTime(time(), $validation, true);
        } else {
            return null;
        }

        return $data;
    }

    /**
     * Formatting data for enum
     * @param string $data Enum to formatting
     * @param array $validation The data for the current data type validation
     * @return string
     * @access private
     * @final
     */
    final private function setEnum($data, $validation)
    {
        if (isset($validation['values']) === false || count($validation['values']) < 1) {
            return null;
        } elseif (isset($validation['default']) && in_array($validation['default'], $validation['values']) === false) {
            return null;
        } elseif (in_array($data, $validation['values']) === false
                  && isset($validation['default']) && in_array($validation['default'], $validation['values'])) {
            $data = $validation['default'];
        } elseif (in_array($data, $validation['values']) === false) {
            return null;
        }

        return $data;
    }

    /**
     * Installing the specified encoding
     * @param array $data The original dataset
     * @return array
     * @access private
     * @final
     */
    final private function multiIconv($data)
    {
        $encoding = mb_detect_encoding(is_array($data) ? @implode($data) : $data);

        if (is_null($this->encoding) === false && $encoding != false && $encoding != $this->encoding) {
            if (is_array($data)) {
                foreach ($data as $code => $value) {
                    $data[ iconv($encoding, $this->encoding, $code) ] = is_array($value)
                                                                            ? $this->multiIconv($value)
                                                                            : iconv($encoding, $this->encoding, $value);
                }
                return $data;
            } else {
                return iconv($encoding, $this->encoding, $data);
            }
        }

        return $data;
    }

    /**
     * @return string
     * @access public
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     * @return RestNormalizer
     * @access public
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @return boolean
     * @access public
     */
    public function getClear()
    {
        return $this->clear;
    }

    /**
     * @param boolean $clear
     * @return RestNormalizer
     * @access public
     */
    public function setClear($clear)
    {
        $this->clear = $clear;

        return $this;
    }

    /**
     * @return string
     * @access public
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @param string $logFile
     * @return RestNormalizer
     * @access public
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;

        return $this;
    }

    /**
     * Preprocessing data
     * @param array $data
     * @return array
     * @access protected
     */
    protected function onPreNormalize($data)
    {
    }
    
    /**
     * Post-processing of data
     * @param array $data
     * @return array
     * @access protected
     */
    protected function onPostNormalize($data)
    {
    }
}
