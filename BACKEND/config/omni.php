<?php

return [
   'INCIDENT_CREATE' => env('INCIDENT_CREATE', 1007325602525804118),
   'INCIDENT_UPDATE' => env('INCIDENT_UPDATE', 1007251407017278811),
   'INCIDENT_RESOLUTION' => env('INCIDENT_RESOLUTION', 1007071499087761958),
   'INCIDENT_FEEDBACK' => env('INCIDENT_FEEDBACK', 1007667463449182703),
   'PROVISIONING_OTP' => env('PROVISIONING_OTP', 1007300339468499953),

   'PER_PAGE' => env('PER_PAGE', 10),
   'DEFAULT_PAGE' => env('DEFAULT_PAGE', 1),
   'ALL_PAGE' => env('ALL_PAGE', 'all'),

   'CHAT_360_EMAIL' => env('CHAT_360_EMAIL', 'prajapati.hitesh@stl.tech'),
   'CHAT_360_PASSWORD' => env('CHAT_360_PASSWORD', 'Neoxomni@2025'),
   'CHAT_360_API_KEY' => env('CHAT_360_API_KEY', 'eGsKut2S.nZMML3Ipj7kgZltmEV22BcGR9t15M4sU'),
   'CHAT_360_URL' => env('CHAT_360_URL', 'https://app.chat360.io/'),
   'CHAT_FROM_PHONE' => env('CHAT_FROM_PHONE', "919712900567"),

   'REPORT_DATA_PER_PAGE' => 50,

   'RECORDING_FILE_PATH' => env('RECORDING_FILE_PATH', 'storage/app/public/recordings'),
   'CHATBOT_URL' => env('CHATBOT_URL', 'http://10.121.25.165:8000'),

   'RETRY_ATTEMPTS' => env('RETRY_ATTEMPTS', 3),
   'RETRY_WAIT' => env('RETRY_WAIT', 500),
   'TIMEOUT' => env('TIMEOUT', 360),

   'API_LIMIT' => (int) env('API_LIMIT', 120),

   // For ticket creation using Whatsapp -> as in 131 Server
   'product_id' => 1,   // Hosted
   'department_id' => 1,   // Sales
   'account_id' => 3,   // Account 1
   'category_id' => 1,  // Pre-Sales
   'incident_id' => 1,  // Hardware Issue
   'campaign_id' => 0,  // 
   'inbound_group_id' => 0,   //
   'default_type' => 2, // Other
   'default_priority' => 1,   // High
   'default_status' => 1,  // Open

   // NOC-995 To-Do Hosted MultiTenant START
   'IS_DC_MULTITENANT' => env('IS_DC_MULTITENANT', 0),
   'HOSTED_URL' => env('HOSTED_URL', "http://localhost:8443"),
   'TENANT_AUTH_USER' => env('TENANT_AUTH_USER', "admin"),
   'TENANT_AUTH_PASS' => env('TENANT_AUTH_PASS', "Admin@123"),
   // NOC-995 To-Do Hosted MultiTenant END
];
