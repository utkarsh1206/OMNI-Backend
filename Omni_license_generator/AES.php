<?php

###############################################################################
# AES Encryption and Decryption algorithm for contact center license generation
# Jira ID : NEOX-267
###############################################################################

//TODO EC following parameter are reuired by AES encryption and decryption algorithm
error_reporting(E_ALL);
$inputKey = exec('/usr/bin/hostid');

#$inputKey = "6a0ad501";

$blockSize = 256;
$seprator = '##$@@$##';

/**
Aes encryption
 */

class AES
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

	function __construct($data = null, $key = null, $blockSize = null, $mode = null)
	{
		$this->setData($data);
		$this->setKey($key);
		$this->setBlockSize($blockSize);
		$this->setMode($mode);
		$this->setIV("");
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function setKey($key)
	{
		//$this->key = $key;
		$this->key = hash('sha256', $key);
	}

	// public function setBlockSize($blockSize)
	// {
	// 	switch ($blockSize) {
	// 		case 128:
	// 			$this->cipher = MCRYPT_RIJNDAEL_128;
	// 			break;
	// 		case 192:
	// 			$this->cipher = MCRYPT_RIJNDAEL_192;
	// 			break;
	// 		case 256:
	// 			/*TODO added cipher for 256 blocksize which is default in our system, NEOX-2687*/
	// 			//$this->cipher = MCRYPT_RIJNDAEL_256;
	// 			$this->cipher = "AES-256-CBC";
	// 			/*END NEOX-2687*/

	// 			break;
	// 	}
	// }

	public function setBlockSize($blockSize)
	{
		switch ($blockSize) {
			case 128:
				// AES-128 is the equivalent for 128-bit block size in OpenSSL
				$this->cipher = 'aes-128-cbc';  // AES-128 with CBC mode
				break;
			case 192:
				// AES-192 is the equivalent for 192-bit block size in OpenSSL
				$this->cipher = 'aes-192-cbc';  // AES-192 with CBC mode
				break;
			case 256:
				// AES-256 for 256-bit block size
				$this->cipher = 'aes-256-cbc';  // AES-256 with CBC mode
				break;
			default:
				// Default to AES-256 if block size is not recognized
				$this->cipher = 'aes-256-cbc';
				break;
		}
	}

	// public function setMode($mode)
	// {
	// 	switch ($mode) {
	// 		case AES::M_CBC:
	// 			$this->mode = MCRYPT_MODE_CBC;
	// 			break;
	// 		case AES::M_CFB:
	// 			$this->mode = MCRYPT_MODE_CFB;
	// 			break;
	// 		case AES::M_ECB:
	// 			$this->mode = MCRYPT_MODE_ECB;
	// 			break;
	// 		case AES::M_NOFB:
	// 			$this->mode = MCRYPT_MODE_NOFB;
	// 			break;
	// 		case AES::M_OFB:
	// 			$this->mode = MCRYPT_MODE_OFB;
	// 			break;
	// 		case AES::M_STREAM:
	// 			$this->mode = MCRYPT_MODE_STREAM;
	// 			break;
	// 		default:
	// 			$this->mode = MCRYPT_MODE_ECB;
	// 			break;
	// 	}
	// }

	public function setMode($mode)
	{
		switch ($mode) {
			case AES::M_CBC:
				$this->mode = 'aes-128-cbc';  // OpenSSL mode for AES CBC
				break;
			case AES::M_CFB:
				$this->mode = 'aes-128-cfb';  // OpenSSL mode for AES CFB
				break;
			case AES::M_ECB:
				$this->mode = 'aes-128-ecb';  // OpenSSL mode for AES ECB
				break;
			case AES::M_NOFB:
				// No equivalent mode in OpenSSL, consider handling differently
				$this->mode = 'aes-128-ecb';  // Default or fallback mode
				break;
			case AES::M_OFB:
				$this->mode = 'aes-128-ofb';  // OpenSSL mode for AES OFB
				break;
			case AES::M_STREAM:
				// OpenSSL doesn't have an explicit 'stream' mode for AES,
				// AES can be used in modes like CTR for stream-like behavior
				$this->mode = 'aes-128-ctr';  // Counter mode (CTR) for stream-like encryption
				break;
			default:
				$this->mode = 'aes-128-ecb';  // Default mode (ECB)
				break;
		}
	}


	public function validateParams()
	{
		if ($this->data != null && $this->key != null && $this->cipher != null) {
			return true;
		} else {
			return FALSE;
		}
	}

	public function setIV($IV)
	{
		$this->IV = $IV;
	}

	protected function getIV()
	{
		if ($this->IV == "") {
			/*TODO here deprecated function are changed with openssl functions, NEOX-2687*/
			/*$this->IV = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);*/
			// $this->IV = substr(hash('sha256', openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher))));
			/*END NEOX-2687*/

			// Get the IV length for the selected cipher (e.g., AES)
			$ivLength = openssl_cipher_iv_length($this->cipher);

			// Generate a cryptographically secure IV
			$this->IV = openssl_random_pseudo_bytes($ivLength);
		}
		return $this->IV;
	}

	public function encrypt()
    {
        if ($this->validateParams()) {
            // Encrypt the data using OpenSSL and the generated IV
            $iv = $this->getIV();  // Generate IV
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
        if ($this->validateParams()) {
            // Extract the IV and encrypted data
            list($encryptedData, $iv) = explode('::', base64_decode($this->data), 2);
            // Decrypt the data using OpenSSL and the provided IV
            $decryptedData = openssl_decrypt($encryptedData, $this->cipher, $this->key, 0, $iv);
            if ($decryptedData === false) {
                throw new Exception('Decryption failed');
            }
            return trim($decryptedData);  // Return the decrypted data
        } else {
            throw new Exception('Invalid parameters for decryption');
        }
    }
}
