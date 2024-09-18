<?php

/**
 * Copyright 2015-2016 Unicity International
 * Copyright 2011-2012 Spadefoot Team
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Unicity\Barcode\QR;

use Unicity\Barcode;
use Unicity\Core;
use Unicity\Throwable;

/**
 * This class is used to create a Quick Response (QR) barcode.
 *
 * @access public
 * @class
 * @package Barcode
 *
 * @see http://en.wikipedia.org/wiki/QR_code
 */
class Creator extends Barcode\Creator
{
    /**
     * This variable stores the encoding.
     *
     * @access protected
     * @var string
     */
    protected $encoding;

    /**
     * This variable stores the margin around the image.
     *
     * @access protected
     * @var integer
     */
    protected $margin;

    /**
     * This variable stores the height and width of the image.
     *
     * @access protected
     * @var integer
     */
    protected $size;

    /**
     * Initializes this bar code creator.
     *
     * @access public
     * @param string $data the data to be encoded (up to 2K in size)
     * @param string $charset the character set to be used
     * @param integer $width the width of the barcode
     * @param integer $height the height of the barcode
     * @param integer $margin the margin around the barcode
     * @throws Throwable\InvalidArgument\Exception indicates that the data could not
     *                                             be encoded
     */
    public function __construct($data, $charset = 'UTF-8', $width = 150, $height = 150, $margin = 4)
    {
        $this->data = Core\Convert::toString($data);
        $this->encoding = $charset;
        $this->size = min($width, $height);
        $this->margin = $margin;
    }

    /**
     * This destructor ensures that any resources are properly disposed.
     *
     * @access public
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->encoding);
        unset($this->margin);
        unset($this->size);
    }

    /**
     * This method returns the value associated with the specified property.
     *
     * @access public
     * @override
     * @param string $name the name of the property
     * @return mixed the value of the property
     * @throws Throwable\InvalidProperty\Exception indicates that the specified property
     *                                             is either inaccessible or undefined
     */
    public function __get($name)
    {
        switch ($name) {
            case 'encoding':
                return $this->encoding;
            case 'file':
                return $this->file;
            case 'height':
            case 'width':
                return $this->size;
            case 'margin':
                return $this->margin;
            default:
                throw new Throwable\InvalidProperty\Exception('Unable to get the specified property. Property ":name" is either inaccessible or undefined.', [':name' => $name]);
        }
    }

    /**
     * This method renders the barcode as a string.
     *
     * @access public
     * @return mixed the barcode
     *
     * @see http://google-code-updates.blogspot.com/2008/07/qr-codes-now-available-on-google-chart.html
     * @see http://code.google.com/apis/chart/docs/gallery/qr_codes.html
     */
    public function render()
    {
        if ($this->image === null) {
            // Encodes the data
            $data = urlencode($this->data);

            // Sets the encoding
            $encoding = $this->encoding;

            // Sets the width/height of the barcode
            $size = $this->size;

            // Sets the error correction level (i.e. 'L', 'M', 'Q', and 'H')
            $loss = 'L';

            // Sets the width (in rows, not pixels) of the white border around the data portion of the chart
            $margin = $this->margin;

            // Creates the URL
            $uri = "http://chart.apis.google.com/chart?chs={$size}x{$size}&cht=qr&chl={$data}&choe={$encoding}&chld={$loss}|{$margin}";

            // Outputs image
            $this->image = file_get_contents($uri);
        }

        return $this->image;
    }

}
