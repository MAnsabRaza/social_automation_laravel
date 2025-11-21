<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CaptchaSolver
{
    /**
     * Solve ReCaptcha V2 (checkbox / invisible) using CapSolver.
     *
     * @param string $siteKey
     * @param string $pageUrl
     * @param int $timeoutSeconds Total timeout to wait (default 120s)
     * @return string gRecaptchaResponse token
     * @throws \Exception
     */
    public static function solveRecaptchaV2(string $siteKey, string $pageUrl, int $timeoutSeconds = 120): string
    {
        $apiKey = env('CAPSOLVER_API_KEY');

        if (!$apiKey) {
            throw new \Exception("CapSolver API Key Missing in .env (CAPSOLVER_API_KEY).");
        }

        // Create Task
        $taskData = [
            "clientKey" => $apiKey,
            "task" => [
                "type" => "ReCaptchaV2TaskProxyless",
                "websiteURL" => $pageUrl,
                "websiteKey" => $siteKey,
            ]
        ];

        $createResp = Http::timeout(20)->post("https://api.capsolver.com/createTask", $taskData);

        if (!$createResp->ok()) {
            throw new \Exception("CapSolver: createTask HTTP error (" . $createResp->status() . ")");
        }

        $createJson = $createResp->json();

        if (isset($createJson['errorId']) && $createJson['errorId'] != 0) {
            $err = $createJson['errorDescription'] ?? json_encode($createJson);
            throw new \Exception("CapSolver createTask error: " . $err);
        }

        if (!isset($createJson['taskId'])) {
            throw new \Exception("CapSolver: createTask did not return taskId. Response: " . json_encode($createJson));
        }

        $taskId = $createJson['taskId'];

        // Poll for result
        $elapsed = 0;
        $interval = 5;

        while ($elapsed < $timeoutSeconds) {
            sleep($interval);
            $elapsed += $interval;

            $resultResp = Http::timeout(20)->post("https://api.capsolver.com/getTaskResult", [
                "clientKey" => $apiKey,
                "taskId" => $taskId
            ]);

            if (!$resultResp->ok()) {
                // ignore transient HTTP errors, but you may log them
                continue;
            }

            $result = $resultResp->json();

            // CapSolver returns status 'processing' or 'ready'
            if (isset($result['status']) && $result['status'] === 'ready') {
                if (isset($result['solution']['gRecaptchaResponse'])) {
                    return $result['solution']['gRecaptchaResponse'];
                } else {
                    throw new \Exception("CapSolver: ready but no gRecaptchaResponse in solution.");
                }
            }

            // If API returns an error
            if (isset($result['errorId']) && $result['errorId'] != 0) {
                $err = $result['errorDescription'] ?? json_encode($result);
                throw new \Exception("CapSolver getTaskResult error: " . $err);
            }
        }

        throw new \Exception("CapSolver Timeout: CAPTCHA not solved within {$timeoutSeconds} seconds.");
    }
}
