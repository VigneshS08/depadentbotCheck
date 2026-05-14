<?php

namespace App\Repositories;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EndatixFormRepository
{
    public function getWebhookConfig(): array
    {
        $endpoint = [
            'Url' => env('ENDATIX_WEBHOOK_URL'),
            'Authentication' => ['Type' => 'None'],
        ];

        return [
            'Events' => [
                'FormCreated' => [
                    'IsEnabled' => true,
                    'WebHookEndpoints' => [$endpoint],
                ],
                'FormUpdated' => [
                    'IsEnabled' => true,
                    'WebHookEndpoints' => [$endpoint],
                ],
                'SubmissionCompleted' => [
                    'IsEnabled' => true,
                    'WebHookEndpoints' => [$endpoint],
                ],
            ],
        ];
    }

    public function login(Request $request, string $method, string $url): mixed
    {
        return Http::$method(env("ENDATIX_URL").$url, [
            "email" => env("ENDATIX_EMAIL"),
            "password" => env("ENDATIX_PASSWORD"),
        ]);
    }

    public function refreshToken(Request $request, string $method, string $url, string $refreshToken)
    {
        return Http::withToken($refreshToken)->acceptJson()->$method(env("ENDATIX_URL").$url);
    }

    public function endatixApi(Request $request, string $method, string $url, string $accessToken)
    {
        return Http::withToken($accessToken)->acceptJson()->$method(env("ENDATIX_URL").$url);
    }

    public function registerWebhooks(array $forms, string $accessToken): void
    {
        $webhookUrl = env('ENDATIX_WEBHOOK_URL');
        if (!$webhookUrl) {
            return;
        }

        $payload = ['webHookSettingsJson' => json_encode($this->getWebhookConfig())];

        foreach ($forms as $form) {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->patch(env("ENDATIX_URL")."/forms/".$form['id'], $payload);

            if (!$response->ok()) {
                Log::warning('Webhook registration failed', [
                    'form_id' => $form['id'],
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        }
    }
}
