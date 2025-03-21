<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\TitoWebhookRequest;
use Illuminate\Support\Facades\Validator;
use App\Jobs\ProcessWebhook;
use Illuminate\Support\Facades\Cache;


class TitoWebhookController extends Controller
{

    /**
     * Handle the incoming webhook request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {


        // Check if the sync is running
        if (Cache::get('is_syncing', false)) {
            Log::channel('webhook')->info('Sync in progress. Please try again later.', $request->all());
            return response()->json(['message' => 'Sync in progress. Please try again later.'], 429);
        }
        Log::channel('webhook')->info('Incoming Webhook Request:', $request->all());
        // Manually validate the request using the rules from TitoWebhookRequest
        $validator = Validator::make($request->all(), (new TitoWebhookRequest())->rules());

        if ($validator->fails()) {
            Log::channel('webhook')->error('Validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => 'Validation failed', 'messages' => $validator->errors()], 422);
        }

        // validate webhook data
        $payload = $validator->validated();
        Log::channel('webhook')->info('Tito Webhook Payload:', $payload);
        Log::channel('webhook')->info('Tito Webhook header:', $request->header());

        $trigger = $request->header('x-webhook-name');
        $signature = $request->header('tito-signature');
        $stringPayload = $request->getContent();

        if (!$this->verifySignature($stringPayload, $signature)) {
            return response()->json(['error' => __('validation.invalid_signature')], 401);
        }

        return $this->processTicket($payload, $trigger);
    }

    /**
     * Verify the signature of the incoming request.
     *
     * @param string $stringPayload
     * @param string $signature
     * @return bool
     */
    private function verifySignature(string $stringPayload, string $signature)
    {
        if (empty($stringPayload) || empty($signature)) {
            Log::channel('webhook')->error("Tito Webhook Data is empty.");
            return false;
        }
        $computedSignature = base64_encode(hash_hmac('sha256', $stringPayload, config('tito.webhookSecret'), true));
        if ($signature !== $computedSignature) {
            Log::channel('webhook')->warning("Invalid signature: {$signature}, computed: {$computedSignature}");
            return false;
        }
        return true;
    }

    /**
     * Process the ticket based on the webhook payload.
     *
     * @param array $payload
     * @param string $trigger
     * @return \Illuminate\Http\JsonResponse
     */
    private function processTicket(array $payload, string $trigger)
    {

        Log::channel('webhook')->info("Processing ticket data:", $payload);
        Log::channel('webhook')->info("Ticket trigger: {$trigger}");

        $payload = array_merge($payload, ['trigger' => $trigger]);

        ProcessWebhook::dispatch($payload)->onQueue('webhooks');
    }

}
