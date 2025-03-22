<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class TitoService
{
    /**
     * @var array
     */
    protected $titoEnv;

    /**
     * prepare env variables
     *
     */
    public function __construct()
    {
        $this->titoEnv = [
            'apiBase' => env('TITO_API_BASE', 'https://api.tito.io/v3'),
            'apiKey' => env('TITO_API_KEY'),
            'apiAccount' => env('TITO_API_ACCOUNT'),
            'apiEvent' => env('TITO_API_EVENT')
        ];
    }

    /**
     * @param $query
     * @return array|mixed
     * @throws ValidationException
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function fetchTitoTickets($query)
    {

        try {
            $validator = Validator::make($this->titoEnv, [
                'apiBase' => 'required|string',
                'apiKey' => 'required|string',
                'apiAccount' => 'required|string',
                'apiEvent' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $query = !empty($query) ? '?' . implode('&', $query) : '';
            $url = "{$this->titoEnv['apiBase']}/{$this->titoEnv['apiAccount']}/{$this->titoEnv['apiEvent']}/tickets{$query}";
            $response = Http::retry(3, 1000)->withHeaders([
                'Authorization' => 'Bearer ' . $this->titoEnv['apiKey']
            ])->get($url);

            if ($response->successful())
                return $response->json();

            throw new NotFoundHttpException();

        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param $email
     * @return array|mixed
     * @throws ValidationException
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function fetchAttendeeTickets($email)
    {
        try {
            $validator = Validator::make($this->titoEnv, [
                'apiBase' => 'required|string',
                'apiKey' => 'required|string',
                'apiAccount' => 'required|string',
                'apiEvent' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            $url = "{$this->titoEnv['apiBase']}/{$this->titoEnv['apiAccount']}/{$this->titoEnv['apiEvent']}/tickets?search[q]={$email}";
            $response = Http::retry(3, 1000)->withHeaders([
                'Authorization' => 'Bearer ' . $this->titoEnv['apiKey']
            ])->get($url);

            if ($response->successful())
                return $response->json();

            throw new NotFoundHttpException();

        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
