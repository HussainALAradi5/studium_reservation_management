<?php

declare(strict_types=1);

namespace App\Controllers\Regional;

use App\Controllers\Base\Controller;
use App\Dtos\Regional\CityFormDto;
use App\Dtos\Regional\CityQuery;
use App\Services\Regional\CityService;
use App\Utilities\Http\Request;

/** Admin city management; create/edit happen in a dialog over the list. */
final class CityController extends Controller
{
    public function index(Request $request): void
    {
        $this->render($request, 'cities/index', [
            'title' => 'Cities',
            'page' => (new CityService())->list(CityQuery::fromQuery($request->queryAll())),
            'filters' => $request->queryAll(),
        ]);
    }

    public function store(Request $request): void
    {
        $city = (new CityService())->create(CityFormDto::fromInput($request->all()));
        $this->done("City \"{$city->name}\" added.", null, 201, ['city' => $city->toArray()]);
    }

    public function update(Request $request): void
    {
        $city = (new CityService())->update($request->param('code'), CityFormDto::fromInput($request->all()));
        $this->done("City \"{$city->name}\" updated.", null, 200, ['city' => $city->toArray()]);
    }

    public function destroy(Request $request): void
    {
        (new CityService())->delete($request->param('code'));
        $this->done('City deleted.');
    }
}
