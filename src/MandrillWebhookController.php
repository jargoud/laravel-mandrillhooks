<?php

namespace EventHomes\Api\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class MandrillWebhookController extends Controller
{
    /**
     * Handle the Mandrill webhook and call method if available
     */
    public function handleWebHook(Request $request): Response
    {
        if ($this->validateSignature($request)) {
            $events = $this->getJsonPayloadFromRequest($request);

            foreach ($events as $event) {
                $eventName = $event['event'] ?? 'undefined';
                if ($eventName == 'undefined' && isset($event['type'])) {
                    $eventName = $event['type'];
                }
                $method = 'handle' . Str::studly(str_replace('.', '_', $eventName));

                if (method_exists($this, $method)) {
                    $this->{$method}($event);
                }
            }

            return new Response;
        }

        return new Response('Unauthorized', 401);
    }

    /**
     * Validates the signature of a mandrill request if key is set
     */
    private function validateSignature(Request $request): bool
    {
        $webhook_key = config('mandrill-webhooks.webhook_key');

        if (!empty($webhook_key)) {
            $signature = $this->generateSignature($webhook_key, $request->url(), $request->all());
            return $signature === $request->header('X-Mandrill-Signature');
        }

        return true;
    }

    /**
     * Generates a base64-encoded signature for a Mandrill webhook request.
     *
     * @see https://mandrill.zendesk.com/hc/en-us/articles/205583257-How-to-Authenticate-Webhook-Requests
     */
    public function generateSignature(string $webhook_key, string $url, array $params): string
    {
        $signed_data = $url;
        ksort($params);
        foreach ($params as $key => $value) {
            $signed_data .= $key;
            $signed_data .= $value;
        }

        return base64_encode(hash_hmac('sha1', $signed_data, $webhook_key, true));
    }

    /**
     * Pull the Mandrill payload from the json
     */
    private function getJsonPayloadFromRequest(Request $request): array
    {
        return (array)json_decode($request->get('mandrill_events'), true);
    }
}
