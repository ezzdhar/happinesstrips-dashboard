<?php

namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationFirebaseHelper
{
    public static function send($user, array $data): void
    {
        // If $user is an ID, fetch the user model
        if (! $user instanceof User) {
            $user = User::find($user);
        }

        if (! $user || ! $user->fcm_token) {
            Log::warning('User not found or FCM token missing', ['user_id' => $user->id ?? $user]);

            return;
        }

        try {
            $credentialsFilePath = storage_path('firebase.json');

            if (! file_exists($credentialsFilePath)) {
                Log::error('Firebase credentials file not found', ['path' => $credentialsFilePath]);

                return;
            }

            $client = new GoogleClient;
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();
            $token = $client->getAccessToken();

            if (! $token || ! isset($token['access_token'])) {
                Log::error('Failed to get access token from Google Client');

                return;
            }

            $access_token = $token['access_token'];

            $payload = [
                'message' => [
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $data['title'] ?? '',
                        'body' => $data['body'] ?? '',
                    ],
                    'data' => [
                        'id' => (string) ($data['type']['id'] ?? ''),
                        'name' => $data['type']['name'] ?? '',
                        'procedure' => $data['type']['procedure'] ?? '',
                    ],
                    'android' => [
                        'notification' => [
                            'sound' => 'default',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ],
            ];

            $projectId = config('fcm.project_id', 'happiness-597ed');
            $response = Http::withHeaders([
                'Authorization' => "Bearer $access_token",
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

            if ($response->failed()) {
                $responseBody = $response->json();
                $errorCode = $responseBody['error']['details'][0]['errorCode'] ?? null;

                // If token is unregistered, clear it from database
//                if ($errorCode === 'UNREGISTERED') {
//                    Log::warning('FCM token is unregistered, clearing from database', [
//                        'user_id' => $user->id,
//                        'token' => $user->fcm_token,
//                    ]);
//
//                    $user->update(['fcm_token' => null]);
//                }

                Log::error('FCM notification failed', [
                    'user_id' => $user->id,
                    'token' => $user->fcm_token,
                    'status' => $response->status(),
                    'error_code' => $errorCode,
                    'response' => $response->body(),
                ]);
            } else {
                Log::info('FCM notification sent successfully', [
                    'user_id' => $user->id,
                    'response' => $response->json(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM notification exception', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
