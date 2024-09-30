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

    const DISTRICT = [
        ["kode" => "*", "id" => "*", "name" => "Semua Kecamatan", "level" => "2"],
        ["kode" => "357601", "id" => "PRAJURITKULON", "name" => "PRAJURITKULON", "level" => "3"],
        ["kode" => "357602", "id" => "MAGERSARI", "name" => "MAGERSARI", "level" => "3"],
        ["kode" => "357603", "id" => "KRANGGAN", "name" => "KRANGGAN", "level" => "3"],
    ];

    const SUB_DISTRICT = [
        ["kode" => "*", "id" => "*", "nama" => "Semua Kelurahan", "level" => "3"],
        ["kode" => "3576021009", "id" => "KEDUNDUNG", "nama" => "KEDUNDUNG", "level" => "4", "puskesmas" => "KEDUNDUNG", "kecamatan" => "357602"],
        ["kode" => "3576021010", "id" => "WATES", "nama" => "WATES", "level" => "4", "puskesmas" => "WATES", "kecamatan" => "357602"],
        ["kode" => "3576031001", "id" => "KRANGGAN", "nama" => "KRANGGAN", "level" => "4", "puskesmas" => "KRANGGAN", "kecamatan" => "357603"],
        ["kode" => "3576031002", "id" => "MIJI", "nama" => "MIJI", "level" => "4", "puskesmas" => "MENTIKAN", "kecamatan" => "357603"],
        ["kode" => "3576031003", "id" => "MERI", "nama" => "MERI", "level" => "4", "puskesmas" => "KRANGGAN", "kecamatan" => "357603"],
        ["kode" => "3576031004", "id" => "JAGALAN", "nama" => "JAGALAN", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357603"],
        ["kode" => "3576031005", "id" => "SENTANAN", "nama" => "SENTANAN", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357603"],
        ["kode" => "3576021008", "id" => "BALONGSARI", "nama" => "BALONGSARI", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357602"],
        ["kode" => "3576021004", "id" => "GEDONGAN", "nama" => "GEDONGAN", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357602"],
        ["kode" => "3576011003", "id" => "MENTIKAN", "nama" => "MENTIKAN", "level" => "4", "puskesmas" => "MENTIKAN", "kecamatan" => "357601"],
        ["kode" => "3576011004", "id" => "KAUMAN", "nama" => "KAUMAN", "level" => "4", "puskesmas" => "MENTIKAN", "kecamatan" => "357601"],
        ["kode" => "3576011005", "id" => "PULOREJO", "nama" => "PULOREJO", "level" => "4", "puskesmas" => "MENTIKAN", "kecamatan" => "357601"],
        ["kode" => "3576011006", "id" => "PRAJURITKULON", "nama" => "PRAJURITKULON", "level" => "4", "puskesmas" => "BLOOTO", "kecamatan" => "357601"],
        ["kode" => "3576011007", "id" => "SURODINAWAN", "nama" => "SURODINAWAN", "level" => "4", "puskesmas" => "BLOOTO", "kecamatan" => "357601"],
        ["kode" => "3576011008", "id" => "BLOOTO", "nama" => "BLOOTO", "level" => "4", "puskesmas" => "BLOOTO", "kecamatan" => "357601"],
        ["kode" => "3576021001", "id" => "GUNUNGGEDANGAN", "nama" => "GUNUNGGEDANGAN", "level" => "4", "puskesmas" => "KEDUNDUNG", "kecamatan" => "357602"],
        ["kode" => "3576021003", "id" => "MAGERSARI", "nama" => "MAGERSARI", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357602"],
        ["kode" => "3576031006", "id" => "PURWOTENGAH", "nama" => "PURWOTENGAH", "level" => "4", "puskesmas" => "GEDONGAN", "kecamatan" => "357603"],
    ];

    const PUSKESMAS = [
        ['id' => 'BLOOTO', 'nama' => 'PUSKESMAS BLOOTO'],
        ['id' => 'MENTIKAN', 'nama' => 'PUSKESMAS MENTIKAN'],
        ['id' => 'KEDUNDUNG', 'nama' => 'PUSKESMAS KEDUNDUNG'],
        ['id' => 'GEDONGAN', 'nama' => 'PUSKESMAS GEDONGAN'],
        ['id' => 'WATES', 'nama' => 'PUSKESMAS WATES'],
        ['id' => 'KRANGGAN', 'nama' => 'PUSKESMAS KRANGGAN']
    ];

    /**
     * @param Request $request
     * @return Response
     */
    public function getDistricts(Request $request): \Winata\Core\Response\Http\Response
    {
        // SELECT replace(replace(replace(id, '.0000', ''), '.00', ''), '.', '') kode, upper(nama) nama, level FROM `wilayah` WHERE id like '35.76%' and level > 1;

        return $this->response(self::DISTRICT);

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getSubDistricts(Request $request): Response
    {
        $validated = $request->validate([
            'type_id' => ['nullable', 'string'],
            'type' => ['nullable', 'in:health_center,districts']
        ]);

        $data = match ($validated['type']) {
            'health_center' => collect(self::SUB_DISTRICT)
                ->where('puskesmas', '=', $validated['type_id']),
            'districts' => collect(self::SUB_DISTRICT)
                ->where('kecamatan', '=', $validated['type_id']),
            default => [],
        };

        return $this->response($data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getHealthCenter(Request $request): Response
    {
        return $this->response(self::PUSKESMAS);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getGenders(Request $request): Response
    {
        $data = collect(Gender::cases())->map(function (Gender $item) {
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
        $data = collect(Cluster::cases())->map(function (Cluster $item) {
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
        $data = collect(Target::cases())->map(function (Target $item) {
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
        $data = collect($serviceTargets)->map(function (Service $item) {
            return [
                'id' => $item->value,
                'name' => $item->label(),
            ];
        });

        return $this->response($data);
    }
}
