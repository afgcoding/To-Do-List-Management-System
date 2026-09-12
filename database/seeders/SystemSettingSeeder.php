<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'company_name' => SystemSetting::DEFAULT_COMPANY_NAME,
                'date_format' => SystemSetting::DEFAULT_DATE_FORMAT,
                'time_zone' => SystemSetting::DEFAULT_TIME_ZONE,
                'primary_color' => SystemSetting::DEFAULT_PRIMARY_COLOR,
            ],
        );
    }
}
