<?php

namespace Database\Seeders;

use App\Enums\ProviderType;
use App\Models\ApiConfiguration;
use App\Models\SmsProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Chat360ApiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = $this->createProviders("Chat360 Auth Token");
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

        $this->saveConfiguration($providers->id, $configurations);

        $providers = $this->createProviders("Chat360 Fetch Message");
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
        $this->saveConfiguration($providers->id, $data);


        $providers = $this->createProviders("Chat360 Send Message");
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
        $this->saveConfiguration($providers->id, $data);
    }

    protected function createProviders($value)
    {
        return SmsProvider::updateOrCreate([
            "name" => "Chat360 Auth Token",
            "type" => ProviderType::CHATBOT->value,
        ]);
    }

    protected function saveConfiguration($providerId, $configurations)
    {
        foreach ($configurations as $config) {
            foreach ($config as $key => $value) {
                ApiConfiguration::updateOrCreate(
                    [
                        'provider_id' => $providerId,
                        'api_config_type_id' => ProviderType::CHATBOT->value,
                    ],
                    [
                        'config_key' => $key,
                        'config_value' => $value,
                    ]
                );
            }
        }
    }
}
