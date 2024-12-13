<?php
namespace App\Services;

use Exception;

class LicenseService
{
    protected $aesService;

    // Constructor to inject AESService
    public function __construct(AESService $aesService)
    {
        $this->aesService = $aesService;
    }

    // Method to encode (encrypt) the license key
    public function encodeLicense($validityDate, $numberOfAgents, $numberOfLiveAgents, $numberOfAclUsers)
    {
        // Generate the hostid (used as input key for encryption)
        $inputKey = exec('/usr/bin/hostid');

        try {
            $EvalidityDate = $this->aesService->encryptData($validityDate, $inputKey);
            $EnumberOfAgents = $this->aesService->encryptData($numberOfAgents, $inputKey);
            $EnumberOfLiveAgents = $this->aesService->encryptData($numberOfLiveAgents, $inputKey);
            $EnumberOfAclUsers = $this->aesService->encryptData($numberOfAclUsers, $inputKey);

            // Concatenate encrypted components into a license key
            $separator = '##$@@$##';
            $licenseKey = $EvalidityDate . $separator . $EnumberOfAgents . $separator . $EnumberOfLiveAgents . $separator . $EnumberOfAclUsers;

            return $licenseKey;
        } catch (Exception $e) {
            throw new Exception("Encryption failed: " . $e->getMessage());
        }
    }

    // Method to decode (decrypt) the license key
    public function decodeLicense($licenseKey)
    {
        // Generate the hostid (used as input key for decryption)
        $inputKey = exec('/usr/bin/hostid');

        // License key separator
        $separator = '##$@@$##';

        // Split the license key into its components
        $param = explode($separator, $licenseKey);
        if (count($param) < 4) {
            throw new Exception('Invalid license format');
        }

        try {
            $EvalidityDate = $param[0];
            $EnumberOfAgents = $param[1];
            $EnumberOfLiveAgents = $param[2];
            $EnumberOfAclUsers = $param[3];

            // Decrypt each component using AES
            $validityDate = $this->aesService->decryptData($EvalidityDate, $inputKey);
            $numberOfAgents = $this->aesService->decryptData($EnumberOfAgents, $inputKey);
            $numberOfLiveAgents = $this->aesService->decryptData($EnumberOfLiveAgents, $inputKey);
            $numberOfAclUsers = $this->aesService->decryptData($EnumberOfAclUsers, $inputKey);

            // Return decoded values as an array
            return [
                'validity_date' => $validityDate,
                'number_of_agents' => $numberOfAgents,
                'number_of_live_agents' => $numberOfLiveAgents,
                'number_of_acl_users' => $numberOfAclUsers,
            ];
        } catch (Exception $e) {
            throw new Exception("Decryption failed: " . $e->getMessage());
        }
    }
}
