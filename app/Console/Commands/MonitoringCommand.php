<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MonitoringCommand extends Command
{
    protected $signature = 'monitoring';

    public function handle(): void
    {
        $deviceId = 'ba81fcc7-dd2d-4b09-b591-0c7653b5960a';

        $previousState = null;

        while (true) {
            $response = Http::withToken(config('app.yandex_iot_token'))
                ->get('https://api.iot.yandex.net/v1.0/user/info');

            $this->info('');
            $this->info(now()->toDateTimeString());

            if ($response->successful()) {
                $responseData = $response->json();
                $device = collect($responseData['devices'])->firstWhere('id', $deviceId);

                if ($device) {
                    $properties = collect($device['properties']);
                    $temperature = $properties->firstWhere('parameters.instance', 'temperature')['state']['value'] ?? null;
                    $humidity = $properties->firstWhere('parameters.instance', 'humidity')['state']['value'] ?? null;
                    $batteryLevel = $properties->firstWhere('parameters.instance', 'battery_level')['state']['value'] ?? null;

                    $currentState = [
                        'temperature' => $temperature,
                        'humidity' => $humidity,
                        'batteryLevel' => $batteryLevel,
                    ];

                    if ($previousState !== null && $previousState !== $currentState) {
                        $this->warn('ВНИМАНИЕ: Значения устройства изменились!');
                        $this->warn(
                            sprintf(
                                'Температура: %s → %s, Влажность: %s → %s, Уровень заряда: %s → %s',
                                $previousState['temperature'],
                                $currentState['temperature'],
                                $previousState['humidity'],
                                $currentState['humidity'],
                                $previousState['batteryLevel'],
                                $currentState['batteryLevel']
                            )
                        );
                    }

                    $previousState = $currentState;

                    $this->info(sprintf('Температура: %s °C', $temperature));
                    $this->info(sprintf('Влажность: %s %%', $humidity));
                    $this->info(sprintf('Уровень заряда: %s %%', $batteryLevel));
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
}
