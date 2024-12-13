<?php

namespace App\Services;

use Exception;

class AESService
{
    const M_CBC = 'cbc';
    const M_CFB = 'cfb';
    const M_ECB = 'ecb';
    const M_NOFB = 'nofb';
    const M_OFB = 'ofb';
    const M_STREAM = 'stream';

    protected $key;
    protected $cipher;
    protected $data;
    protected $mode;
    protected $IV;

    public function __construct($data = null, $key = null, $blockSize = null, $mode = null)
    {
        $this->setData($data);
        $this->setKey($key);
        $this->setBlockSize($blockSize);
        $this->setMode($mode);
        // $this->setIV("");
        $this->IV = null;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setKey($key)
    {
        $this->key = hash('sha256', $key);
    }

    public function setBlockSize($blockSize)
    {
        switch ($blockSize) {
            case 128:
                $this->cipher = 'aes-128-cbc';
                break;
            case 192:
                $this->cipher = 'aes-192-cbc';
                break;
            case 256:
                $this->cipher = 'aes-256-cbc';
                break;
            default:
                $this->cipher = 'aes-256-cbc';
                break;
        }
    }

    public function setMode($mode)
    {
        switch ($mode) {
            case self::M_CBC:
                $this->mode = 'aes-128-cbc';
                break;
            case self::M_CFB:
                $this->mode = 'aes-128-cfb';
                break;
            case self::M_ECB:
                $this->mode = 'aes-128-ecb';
                break;
            case self::M_NOFB:
                $this->mode = 'aes-128-ecb';
                break;
            case self::M_OFB:
                $this->mode = 'aes-128-ofb';
                break;
            case self::M_STREAM:
                $this->mode = 'aes-128-ctr';
                break;
            default:
                $this->mode = 'aes-128-ecb';
                break;
        }
    }

    public function validateParams()
    {
        return ($this->data !== null && $this->key !== null && $this->cipher !== null);
    }

    // public function setIV($IV)
    // {
    //     $this->IV = $IV;
    // }

    protected function getIV()
    {
        if ($this->IV == "") {
            $ivLength = openssl_cipher_iv_length($this->cipher);
            $this->IV = openssl_random_pseudo_bytes($ivLength);
        }
        return $this->IV;
    }

    public function encrypt()
    {
        // if ($this->validateParams()) {
        //     return trim(base64_encode(openssl_encrypt($this->data, $this->cipher, $this->key, 0, $this->IV)));
        // } else {
        //     throw new Exception('Invalid params!');
        // }

        if ($this->validateParams()) {
            $iv = $this->getIV();  // Generate or use the provided IV
            $encryptedData = openssl_encrypt($this->data, $this->cipher, $this->key, 0, $iv);
            if ($encryptedData === false) {
                throw new Exception('Encryption failed');
            }
            // Return the IV and encrypted data together (base64 encoded)
            return base64_encode($encryptedData . '::' . $iv);
        } else {
            throw new Exception('Invalid parameters for encryption');
        }
    }

    public function decrypt()
    {
        // if ($this->validateParams()) {
        //     return trim(openssl_decrypt(base64_decode($this->data), $this->cipher, $this->key, 0, $this->IV));
        // } else {
        //     throw new Exception('Invalid params!');
        // }

        if ($this->validateParams()) {
            list($encryptedData, $iv) = explode('::', base64_decode($this->data), 2);
            $decryptedData = openssl_decrypt($encryptedData, $this->cipher, $this->key, 0, $iv);
            if ($decryptedData === false) {
                throw new Exception('Decryption failed');
            }
            return trim($decryptedData);
        } else {
            throw new Exception('Invalid parameters for decryption');
        }
    }

    public function decryptData($data, $inputKey)
    {
        // Use the input key for decryption
        $this->setKey($inputKey);
        $this->setData($data);
        return $this->decrypt();  // Use the existing decrypt method
    }


    public function encryptData($data, $inputKey)
    {
        $this->setKey($inputKey);
        $this->setData($data);
        return $this->encrypt();
    }
}
