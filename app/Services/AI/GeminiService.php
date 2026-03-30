<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class GeminiService
{

    public static function ask($prompt)
    {

        $apiKey = env('GEMINI_API_KEY');

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=".$apiKey,
            [
                "contents" => [
                    [
                        "parts" => [
                            ["text"=>$prompt]
                        ]
                    ]
                ]
            ]
        );

        if($response->successful()){

            return $response['candidates'][0]['content']['parts'][0]['text'] ?? 'AI tidak memberi respon';

        }

        return $response->body();

    }

}