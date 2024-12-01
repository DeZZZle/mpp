<?php

namespace App\Console\Commands;

use App\Models\Climate;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ImportCommand extends Command
{
    protected $signature = 'import';

    public function handle(): void
    {
        $deviceId = 'ba81fcc7-dd2d-4b09-b591-0c7653b5960a';

        while (true) {
            $response = Http::withToken(config('app.yandex_iot_token'))
                ->get("https://api.iot.yandex.net/v1.0/devices/{$deviceId}");

            $this->info('');
            $this->info(now()->toDateTimeString());

            if ($response->successful()) {
                $responseData = $response->json();
                $device = $responseData;

                if ($device) {
                    $this->processDevice($device);
                } else {
                    $this->error(sprintf('Устройство с ID %s не найдено.', $deviceId));
                }
            } else {
                $this->error(sprintf('Ошибка: %s', $response->status()));
                $this->error($response->body());
            }

            sleep(2.5 * 60);
        }
    }

    protected function processDevice(array $device): void
    {
        $deviceId = Arr::get($device, 'id');
        $properties = collect($device['properties']);
        $temperature = Arr::get($properties->firstWhere('parameters.instance', 'temperature'), 'state.value');
        $humidity = Arr::get($properties->firstWhere('parameters.instance', 'humidity'), 'state.value');
        $batteryLevel = Arr::get($properties->firstWhere('parameters.instance', 'battery_level'), 'state.value');

        $temperatureLastUpdated = when(
            Arr::get($properties->firstWhere('parameters.instance', 'temperature'), 'last_updated'),
            fn($value) => Carbon::createFromTimestamp($value)
        );
        $humidityLastUpdated = when(
            Arr::get($properties->firstWhere('parameters.instance', 'humidity'), 'last_updated'),
            fn($value) => Carbon::createFromTimestamp($value)
        );
        $batteryLevelLastUpdated = when(
            Arr::get($properties->firstWhere('parameters.instance', 'battery_level'), 'last_updated'),
            fn($value) => Carbon::createFromTimestamp($value)
        );

        $this->info(sprintf('Температура: %s °C (обновлено %s)', $temperature, $temperatureLastUpdated));
        $this->info(sprintf('Влажность: %s %% (обновлено %s)', $humidity, $humidityLastUpdated));
        $this->info(sprintf('Уровень заряда: %s %% (обновлено %s)', $batteryLevel, $batteryLevelLastUpdated));

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
