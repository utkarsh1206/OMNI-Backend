<?php

return [
    /**
     * This value indicates the number of digits to be generated
     */
    'digits' => 6,

    /**
     * This value indicates the number of minutes until one OTP will be valid
     */
    'expiry' => 2,

    'throttle' => 3, // <--- ADD THIS LINE FOR MAXIMUM ATTEMPTS
];
