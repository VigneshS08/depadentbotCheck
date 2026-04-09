<?php

namespace App\Repositories;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EndatixFormRepository
{
    public $data = [
        'Events' => [
            'FormCreated' => [
                'IsEnabled' => true,
                'WebHookEndpoints' => [
                    [
                        'Url' => "https://cc8f-115-246-220-132.ngrok-free.app/endatix-response",
                        'Authentication' => [
                            'Type' => 'None',
                        ],
                    ],
                ],
            ],
            'FormUpdated' => [
                'IsEnabled' => true,
                'WebHookEndpoints' => [
                    [
                        'Url' => "https://cc8f-115-246-220-132.ngrok-free.app/endatix-response",
                        'Authentication' => [
                            'Type' => 'None',
                        ],
                    ],
                ],
            ],
            'SubmissionCompleted' => [
                'IsEnabled' => true,
                'WebHookEndpoints' => [
                    [
                        'Url' => "https://cc8f-115-246-220-132.ngrok-free.app/endatix-response",
                        'Authentication' => [
                            'Type' => 'None',
                        ],
                    ],
                ],
            ],
        ],
    ];

    public function login(Request $request,string $method,string $url): mixed
    {
        $response = Http::$method(env("ENDATIX_URL").$url,[
            "email"=>env("ENDATIX_EMAIL"),
            "password"=>env("ENDATIX_PASSWORD")
        ]);

        return $response;
    }

    public function refreshToken(Request $request,string $method,string $url,string $refreshToken)
    {
        $response = Http::withToken($refreshToken)->acceptJson()->$method(env("ENDATIX_URL").$url);
        return $response;
    }

    public function endatixApi(Request $request,string $method,string $url,string $accessToken)
    {
        $response = Http::withToken($accessToken)->acceptJson()->$method(env("ENDATIX_URL").$url);
        // $response=$this->webhooksCall($response,$accessToken);
        return $response;
    }

    public function webhooksCall($response,$accessToken=null)
    {
        if($response->ok())
        {
            foreach($response->json() as $form)
            {
                $payload=[
                    "webHookSettingsJson"=>json_encode($this->data)
                ];
                $webhooksResponse = Http::withToken($accessToken)->acceptJson()->patch(env("ENDATIX_URL")."/forms/".$form['id'],$payload);
                // Log::info(['webhooks'=>$webhooksResponse->json()]);
            }
        }
        return $response;
    }

}
