<?php

namespace App\Services;

use App\Models\MapApiSetting;

class MapService
{
    public function getApiKey()
    {
        return optional(MapApiSetting::first())->api_key;
    }

}