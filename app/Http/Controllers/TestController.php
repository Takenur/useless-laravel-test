<?php

namespace App\Http\Controllers;

use App\Service\JsonDataService;

class TestController extends Controller
{
    public function getMonthsReport(JsonDataService $service)
    {
        return $service->getMonthsReport();
    }

    public function getNomenclatureReport(JsonDataService $service)
    {
        return $service->getNomenclatureReport();
    }

    public function getManagersReport(JsonDataService $service)
    {
        return $service->getManagersReport();
    }
}
