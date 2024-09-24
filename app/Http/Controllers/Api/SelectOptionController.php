<?php

namespace App\Http\Controllers\Api;

use App\Enums\Cluster;
use App\Enums\Gender;
use App\Enums\Service;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Winata\Core\Response\Http\Response;

class SelectOptionController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function getDistricts(Request $request): \Winata\Core\Response\Http\Response
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'id' => 'id' . $i,
                'name' => 'name district ' . $i,
            ];
        }
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getSubDistricts(Request $request): Response
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'id' => 'id' . $i,
                'name' => 'name sub district ' . $i,
            ];
        }
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getHealthCenter(Request $request): Response
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'id' => 'id' . $i,
                'name' => 'name health care ' . $i,
            ];
        }
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getGenders(Request $request): Response
    {
        $data = collect(Gender::cases())->map(function (Gender $item){
            return [
                'id' => $item->value,
                'name' => $item->label(),
            ];
        });
        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getClusters(Request $request): Response
    {
        $data = collect(Cluster::cases())->map(function (Cluster $item){
            return [
                'id' => $item->value,
                'name' => $item->label(),
            ];
        });

        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getTargets(Request $request): Response
    {
        $data = collect(Target::cases())->map(function (Target $item){
            return [
                'id' => $item->value,
                'name' => $item->label(),
            ];
        });

        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getServices(Request $request): Response
    {
        $validated = $request->validate([
            'target' => ['nullable', Rule::in(Target::options())]
        ]);

        $serviceTargets = !empty($validated['target']) ? Target::tryFrom($validated['target'])->serviceLists() : [];
        $data = collect($serviceTargets)->map(function (Service $item){
            return [
                'id' => $item->value,
                'name' => $item->label(),
            ];
        });

        return $this->response($data);
    }
}
