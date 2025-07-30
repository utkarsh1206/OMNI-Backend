<?php

namespace Database\Seeders;

use App\Enums\ProviderType;
use App\Models\ApiConfiguration;
use App\Models\SmsProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Chat360ApiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('providers')->truncate();
        
        DB::table('api_configurations')->truncate();

        $providers = $this->createProviders("Chat360 Auth Token", ProviderType::CHATBOT->value);
        
        $configurations = [
            [
                'method' => 'post',
                'type' => 'json',
                'url' => 'https://app.chat360.io/api/auth/login',
                'query_params' => 'email=prajapati.hitesh@stl.tech&password=Neoxomni@2025',
                'max_length' => '1024',
                'success_code' => '200',
                'enabled' => true,
            ]
        ];

        $this->saveConfiguration($providers->id, $configurations, ProviderType::CHATBOT->value);

        $providers = $this->createProviders("Chat360 Fetch Message", ProviderType::CHATBOT->value);
        $data = [
            [
                'method' => 'get',
                'type' => 'json',
                'url' => 'https://app.chat360.io/api/clientwidget_updated/chatroom/',
                'query_params' => 'APIKEY=eGsKut2S.nZMML3Ipj7kgZltmEV22BcGR9t15M4sU',
                'max_length' => '5000',
                'success_code' => '200',
                'enabled' => true,
            ]
        ];
        $this->saveConfiguration($providers->id, $data, ProviderType::CHATBOT->value);


        $providers = $this->createProviders("Chat360 Send Message", ProviderType::CHATBOT->value);
        $data = [
            [
                'method' => 'post',
                'type' => 'json',
                'url' => 'https://app.chat360.io/api/whatsapp/whatsapp-session-messages',
                'query_params' => 'PHONE="919712900567"&APIKEY=eGsKut2S.nZMML3Ipj7kgZltmEV22BcGR9t15M4sU',
                'max_length' => '5000',
                'success_code' => '200',
                'enabled' => true,
            ]
        ];
        $this->saveConfiguration($providers->id, $data, ProviderType::CHATBOT->value);

        $providers = $this->createProviders("Custom SMS", ProviderType::SMS->value);
        $configurations = [
            [
                'method' => 'get',
                'type' => 'json',
                'url' => 'http://103.16.101.52:80802/bulksms/',
                'query_params' => '?username=0418-stltest&password=stl@2024&type=0&dlr=1&destination=[PHONE_NUMBER]&source=STNEOX&entityid=1101412400000010905',
                'max_length' => '1024',
                'success_code' => '200',
                'enabled' => true,
            ]
        ];

        $this->saveConfiguration($providers->id, $configurations, ProviderType::SMS->value);
    }

    protected function createProviders($value, $type)
    {
        return SmsProvider::updateOrCreate([
            "name" => $value,
            "type" => $type,
        ]);
    }

    protected function saveConfiguration($providerId, $configurations,$type)
    {
        foreach ($configurations as $config) {
            foreach ($config as $key => $value) {
                ApiConfiguration::Create(
                    [
                        'provider_id' => $providerId,
                        'api_config_type_id' => $type,
                        'config_key' => $key,
                        'config_value' => $value,
                    ]
                );
            }
        }
    }
}
