<?php

namespace App\Jobs;

use App\Exceptions\IotException;
use App\Models\Climate;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ClimateImportJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(
        protected string $deviceId
    ) {
    }

    public function handle(): void
    {
        $response = Http::withToken(config('app.yandex_iot_token'))
            ->get("https://api.iot.yandex.net/v1.0/devices/{$this->deviceId}");

        if ($response->successful()) {
            $responseData = $response->json();
            $this->processDevice($responseData);
        } else {
            throw new IotException(sprintf('Ошибка: код %s с сообщением %s ', $response->status(), (string)$response->getBody()));
        }
    }

    protected function processDevice(array $deviceData): void
    {
        $deviceId = Arr::get($deviceData, 'id');
        $properties = collect(Arr::get($deviceData, 'properties'));
        $temperature = Arr::get($properties->firstWhere('parameters.instance', 'temperature'), 'state.value');
        $humidity = Arr::get($properties->firstWhere('parameters.instance', 'humidity'), 'state.value');

        $temperatureLastUpdated = when(
            Arr::get($properties->firstWhere('parameters.instance', 'temperature'), 'last_updated'),
            fn($value) => Carbon::createFromTimestamp($value)
        );
        $humidityLastUpdated = when(
            Arr::get($properties->firstWhere('parameters.instance', 'humidity'), 'last_updated'),
            fn($value) => Carbon::createFromTimestamp($value)
        );

        Climate::firstOrCreate(
            [
                'device_uuid' => $deviceId,
                'type' => 'temperature',
                'last_updated_at' => $temperatureLastUpdated,
            ],
            [
                'value' => $temperature,
            ]
        );

        Climate::firstOrCreate(
            [
                'device_uuid' => $deviceId,
                'type' => 'humidity',
                'last_updated_at' => $humidityLastUpdated,
            ],
            [
                'value' => $humidity,
            ]
        );
    }
}
