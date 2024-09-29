<?php

namespace App\Http\Controllers\Api;

use App\Enums\Cluster;
use App\Enums\Gender;
use App\Enums\Service;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Winata\Core\Response\Http\Response;

class SelectOptionController extends Controller
{
    
    const DISTRICT = [
        ["kode" => "*", "id" => "*", "nama" => "Semua Kecamatan", "level" => "2"],
        ["kode" => "357601", "id" => "PRAJURITKULON", "nama" => "PRAJURITKULON", "level" => "3"],
        ["kode" => "357602", "id" => "MAGERSARI", "nama" => "MAGERSARI", "level" => "3"],
        ["kode" => "357603", "id" => "KRANGGAN", "nama" => "KRANGGAN", "level" => "3"],
    ];

    const SUB_DISTRICT = [
        ["kode" => "*", "id" => "*", "nama" => "Semua Kelurahan", "level" => "3"],
        ["kode" => "3576021009", "id" => "KEDUNDUNG", "nama" => "KEDUNDUNG", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576021010", "id" => "WATES", "nama" => "WATES", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576031001", "id" => "KRANGGAN", "nama" => "KRANGGAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
        ["kode" => "3576031002", "id" => "MIJI", "nama" => "MIJI", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
        ["kode" => "3576031003", "id" => "MERI", "nama" => "MERI", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
        ["kode" => "3576031004", "id" => "JAGALAN", "nama" => "JAGALAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
        ["kode" => "3576031005", "id" => "SENTANAN", "nama" => "SENTANAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
        ["kode" => "3576021008", "id" => "BALONGSARI", "nama" => "BALONGSARI", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576021004", "id" => "GEDONGAN", "nama" => "GEDONGAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576011003", "id" => "MENTIKAN", "nama" => "MENTIKAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576011004", "id" => "KAUMAN", "nama" => "KAUMAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576011005", "id" => "PULOREJO", "nama" => "PULOREJO", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576011006", "id" => "PRAJURITKULON", "nama" => "PRAJURITKULON", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576011007", "id" => "SURODINAWAN", "nama" => "SURODINAWAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576011008", "id" => "BLOOTO", "nama" => "BLOOTO", "level" => "4", "puskesmas" => "", "kecamatan" => "357601"],
        ["kode" => "3576021001", "id" => "GUNUNGGEDANGAN", "nama" => "GUNUNGGEDANGAN", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576021003", "id" => "MAGERSARI", "nama" => "MAGERSARI", "level" => "4", "puskesmas" => "", "kecamatan" => "357602"],
        ["kode" => "3576031006", "id" => "PURWOTENGAH", "nama" => "PURWOTENGAH", "level" => "4", "puskesmas" => "", "kecamatan" => "357603"],
    ];

    const PUSKESMAS = [
        ['id' => 'PUSKESMAS BLOOTO','id' => 'PUSKESMAS BLOOTO'],
        ['id' => 'PUSKESMAS MENTIKAN','id' => 'PUSKESMAS MENTIKAN'],
        ['id' => 'PUSKESMAS KEDUNDUNG','id' => 'PUSKESMAS KEDUNDUNG'],
        ['id' => 'PUSKESMAS GEDONGAN','id' => 'PUSKESMAS GEDONGAN'],
        ['id' => 'PUSKESMAS WATES','id' => 'PUSKESMAS WATES'],
        ['id' => 'PUSKESMAS KRANGGAN','id' => 'PUSKESMAS KRANGGAN']
    ];

    /**
     * @param Request $request
     * @return Response
     */
    public function getDistricts(Request $request): \Winata\Core\Response\Http\Response
    {
        // SELECT replace(replace(replace(id, '.0000', ''), '.00', ''), '.', '') kode, upper(nama) nama, level FROM `wilayah` WHERE id like '35.76%' and level > 1;

        return response()->json(self::DISTRICT);
        /*$result = DB::select('SELECT "Nama Kecamatan" as name
FROM dbEkohortAnc
WHERE "Nama Kecamatan" IS NOT NULL
GROUP BY "Nama Kecamatan";');

        return $this->response(collect($result)->map(function ($item) {
            return [
                'id' => Str::slug(strtolower($item->name), '_'),
                'name' => $item->name,
            ];
        }));*/

    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getSubDistricts(Request $request): Response
    {
        return response()->json(self::SUB_DISTRICT);

        /*$result = DB::select('SELECT "Nama Desa" as name
FROM dbEkohortAnc
WHERE "Nama Desa" IS NOT NULL
GROUP BY "Nama Desa";');

        return $this->response(collect($result)->map(function ($item) {
            return [
                'id' => Str::slug(strtolower($item->name), '_'),
                'name' => $item->name,
            ];
        }));*/
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function getHealthCenter(Request $request): Response
    {
        return response()->json(self::PUSKESMAS);

        /**$result = DB::select('SELECT "Nama Lembaga" as name
FROM dbEkohortAnc
WHERE "Nama Lembaga" IS NOT NULL
GROUP BY "Nama Lembaga";');

        return $this->response(collect($result)->map(function ($item) {
            return [
                'id' => Str::slug(strtolower($item->name), '_'),
                'name' => $item->name,
            ];
        }));*/
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
