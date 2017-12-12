<?php

namespace Tethys\Core;

use Tethys\Utils\StrLib;

class Security extends Component
{

    public $macHash = 'sha256';

    public function validateData($data, $key, $rawHash = false)
    {
        $test = @hash_hmac($this->macHash, '', '', $rawHash);
        if (!$test) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
        }
        $hashLength = StrLib::byteLength($test);
        if (StrLib::byteLength($data) >= $hashLength) {
            $hash = StrLib::byteSubstr($data, 0, $hashLength);
            $pureData = StrLib::byteSubstr($data, $hashLength, null);

            $calculatedHash = hash_hmac($this->macHash, $pureData, $key, $rawHash);

            if ($this->compareString($hash, $calculatedHash)) {
                return $pureData;
            }
        }
        return false;
    }

    public function compareString($expected, $actual)
    {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = StrLib::byteLength($expected);
        $actualLength = StrLib::byteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }

    public function hashData($data, $key, $rawHash = false)
    {
        $hash = hash_hmac($this->macHash, $data, $key, $rawHash);
        if (!$hash) {
            throw new InvalidConfigException('Failed to generate HMAC with hash algorithm: ' . $this->macHash);
        }
        return $hash . $data;
    }

    private $_useLibreSSL;
    private $_randomFile;

    /**
     * @param int $length
     * @return string
     * @throws Exception
     */
    public function generateRandomKey($length = 32)
    {
        if (!is_int($length)) {
            throw new InvalidParamException('First parameter ($length) must be an integer');
        }

        if ($length < 1) {
            throw new InvalidParamException('First parameter ($length) must be greater than 0');
        }

        // always use random_bytes() if it is available
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }

        // The recent LibreSSL RNGs are faster and likely better than /dev/urandom.
        // Parse OPENSSL_VERSION_TEXT because OPENSSL_VERSION_NUMBER is no use for LibreSSL.
        // https://bugs.php.net/bug.php?id=71143
        if ($this->_useLibreSSL === null) {
            $this->_useLibreSSL = defined('OPENSSL_VERSION_TEXT')
                && preg_match('{^LibreSSL (\d\d?)\.(\d\d?)\.(\d\d?)$}', OPENSSL_VERSION_TEXT, $matches)
                && (10000 * $matches[1]) + (100 * $matches[2]) + $matches[3] >= 20105;
        }

        // If not on Windows, try to open a random device.
        if ($this->_randomFile === null && DIRECTORY_SEPARATOR === '/') {
            // urandom is a symlink to random on FreeBSD.
            $device = PHP_OS === 'FreeBSD' ? '/dev/random' : '/dev/urandom';
            // Check random device for special character device protection mode. Use lstat()
            // instead of stat() in case an attacker arranges a symlink to a fake device.
            $lstat = @lstat($device);
            if ($lstat !== false && ($lstat['mode'] & 0170000) === 020000) {
                $this->_randomFile = fopen($device, 'rb') ?: null;

                if (is_resource($this->_randomFile)) {
                    // Reduce PHP stream buffer from default 8192 bytes to optimize data
                    // transfer from the random device for smaller values of $length.
                    // This also helps to keep future randoms out of user memory space.
                    $bufferSize = 8;

                    if (function_exists('stream_set_read_buffer')) {
                        stream_set_read_buffer($this->_randomFile, $bufferSize);
                    }
                    // stream_set_read_buffer() isn't implemented on HHVM
                    if (function_exists('stream_set_chunk_size')) {
                        stream_set_chunk_size($this->_randomFile, $bufferSize);
                    }
                }
            }
        }

        if (is_resource($this->_randomFile)) {
            $buffer = '';
            $stillNeed = $length;
            while ($stillNeed > 0) {
                $someBytes = fread($this->_randomFile, $stillNeed);
                if ($someBytes === false) {
                    break;
                }
                $buffer .= $someBytes;
                $stillNeed -= StrLib::byteLength($someBytes);
                if ($stillNeed === 0) {
                    // Leaving file pointer open in order to make next generation faster by reusing it.
                    return $buffer;
                }
            }
            fclose($this->_randomFile);
            $this->_randomFile = null;
        }

        throw new Exception('Unable to generate a random key');
    }

    /**
     * Masks a token to make it uncompressible.
     * Applies a random mask to the token and prepends the mask used to the result making the string always unique.
     * Used to mitigate BREACH attack by randomizing how token is outputted on each request.
     * @param string $token An unmasked token.
     * @return string A masked token.
     * @since 2.0.12
     */
    public function maskToken($token)
    {
        // The number of bytes in a mask is always equal to the number of bytes in a token.
        $mask = $this->generateRandomKey(StrLib::byteLength($token));
        return StrLib::base64UrlEncode($mask . ($mask ^ $token));
    }

    /**
     * Unmasks a token previously masked by `maskToken`.
     * @param string $maskedToken A masked token.
     * @return string An unmasked token, or an empty string in case of token format is invalid.
     * @since 2.0.12
     */
    public function unmaskToken($maskedToken)
    {
        $decoded = StrLib::base64UrlDecode($maskedToken);
        $length = StrLib::byteLength($decoded) / 2;
        // Check if the masked token has an even length.
        if (!is_int($length)) {
            return '';
        }
        return StrLib::byteSubstr($decoded, $length, $length) ^ StrLib::byteSubstr($decoded, 0, $length);
    }

}