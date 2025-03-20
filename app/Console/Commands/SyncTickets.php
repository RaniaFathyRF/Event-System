<?php

namespace App\Console\Commands;

use App\Jobs\SyncTicketJob;
use Illuminate\Console\Command;
use App\Services\TitoService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Cache;


class SyncTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all tickets from the Tito API into our database';

    /**
     * Execute the console command.
     */
    public function handle(TitoService $titoService)
    {

        ini_set('memory_limit', '512M');
        // Set the sync flag
        Cache::put('is_syncing', true, now()->addMinutes(45)); // Adjust the timeout as needed
        try {
            $page = 1;
            $perPage = 5; // Adjust based on the API limit

            do {
                $queryParameters = $this->buildQueryParameters("created_at", "asc", $page, $perPage, "short");
                $results = $titoService->fetchTitoTickets($queryParameters);

                if (empty($results['tickets'])) {
                    throw new NotFoundHttpException();
                }
                Log::channel("syncTickets")->info('Tickets synchronization loop.' . $page);

                // Dispatch each page's products to a queue job
                SyncTicketJob::dispatch($results['tickets'])->onQueue('syncTickets');

                $meta = $results['meta'];
                $page = !empty($meta['next_page']) ? $meta['next_page'] : ($meta['total_pages'] + 1);
                Log::channel("syncTickets")->info('Tickets synchronization meta.', $meta);

                sleep(1); // Optional: prevent API rate limiting
            } while ($page <= $meta['total_pages']);

            Log::channel("syncTickets")->info('Tickets synchronization loop completed.');

        } catch (\Exception $e) {
            Log::channel("syncTickets")->error('SyncTickets: ' . $e->getMessage());
        } finally {
            // Clear the sync flag
            Cache::forget('is_syncing');
            Log::channel("syncTickets")->info('SyncTickets completed.');
        }
    }

    /**
     * @param string $order
     * @param string $orderBy
     * @param int $page
     * @param int $perPage
     * @param $view
     * @return array
     */
    public function buildQueryParameters(string $order, string $orderBy, int $page, int $perPage, $view)
    {
        return [
            "search[sort]=" . $order ?? "created_at",
            "search[direction]=" . $orderBy ?? "asc",
            "page[number]=" . $page ?? 1,
            "page[size]=" . $perPage ?? 100,
            "view=" . $view ?? "short"
        ];
    }
}
