<?php

namespace App\Http\Controllers\Api;

use App\Enums\Service;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use App\Http\Requests\DefaultRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Winata\Core\Response\Http\Response;

class MainController extends Controller
{
    /**
     * @return Response
     */
    public function summaryPeoples()
    {
        return $this->response([
            'series_penduduk' => [
                'male' => 70490,
                'female' => 71295,
            ], 'series_terlayani' => [
                'male' => 65303,
                'female' => 67541,
            ]
        ]);
    }

    /**
     * @return Response
     */
    public function listKunjungan(): Response
    {
        $data = [];
        $target = Target::cases();
        foreach ($target as $data) {
            $data[] = [
                'name' => $data->name,
                'people_count' => $data->jumlahPenduduk(),
                'service_count' => $data->totalKunjungan(),
            ];
        }
        return $this->response($data);
    }

    /**
     * @return Response
     */
    public function summaryKunjungan(): Response
    {
        $target = Target::cases();
        $totalSasaran = 0;
        $totalSasaranTerlayani = 0;
        $totalSasaranKunjungan = 0;
        foreach ($target as $data) {
            $totalSasaran = $totalSasaran + 1;
            $totalSasaranTerlayani = $totalSasaranTerlayani + $data->totalKunjungan();
            $totalSasaranKunjungan = $totalSasaranKunjungan + $data->totalKunjungan();
        }
        return $this->response([
            'total_sasaran' => $totalSasaran,
            'total_sasaran_terlayani' => $totalSasaranTerlayani,
            'total_sasaran_kunjungan' => $totalSasaranKunjungan,
        ]);
    }

    /**
     * @param DefaultRequest $request
     * @return Response
     */
    public function sasaranTerlayani(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $service = !empty($request->input('indicator')) ? Service::tryFrom($request->input('indicator')) : Service::KUNJUNGAN_ANC_6;
        if (!$service instanceof Service){
            return $this->response();
        }
        $tableName = $service->tableMaps();
        $tableColumn = $service->dateColumn();
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');

        $columnType = DB::selectOne("
    SELECT data_type
    FROM information_schema.columns
    WHERE table_name = :table_name AND column_name = :column_name
", [
            'table_name' => $tableName,
            'column_name' => $tableColumn
        ])->data_type;

        $query = "
    SELECT
        COUNT(\"$tableColumn\") AS count_anc,
";
        if ($columnType == 'character varying') {
            $query .= "TO_DATE(\"$tableColumn\", 'YYYY-MM') AS month ";
        } else {
            $query .= "DATE_TRUNC('month', \"$tableColumn\") AS month ";
        }

        $query .= "
    FROM
        \"$tableName\"
    WHERE 1=1
";

        if (!is_null($startDate)) {
            $query .= " AND \"$tableColumn\" >= :start_date";
        }
        if (!is_null($endDate)) {
            $query .= " AND \"$tableColumn\" <= :end_date";
        }
        $query .= "
    GROUP BY
        month
    ORDER BY
        month ASC
";

        $params = [];
        if (!is_null($startDate)) {
            $params['start_date'] = $startDate;
        }
        if (!is_null($endDate)) {
            $params['end_date'] = $endDate;
        }

        /**
         * "     SELECT COUNT(\"Tgl Persalinan\") AS count_anc, DATE_TRUNC('month', TO_DATE(\"Tgl Persalinan\", 'YYYY-MM-DD')) AS month      FROM         \"dbEkohortPersalinan\"     WHERE 1=1     GROUP BY         month "
         * */

        $results = DB::select($query, $params);

        if ($request->input('aggregate') == 'absolute'){
            return $this->response(collect($results)->map(function ($item) {
                return [
                    'count' => $item->count_anc,
                    'name' => Carbon::parse($item->month)->format('F'),
                ];
            }));
        }

        $data = [];
        $count = 0;
        foreach (collect($results) as $item){
            $count = $count + $item->count_anc;
            $data[] = [
                'count' => $count,
                'name' => Carbon::parse($item->month)->format('F'),
            ];
        }

        return $this->response($data);
    }

    /**
     * @param DefaultRequest $request
     * @return Response
     */
    public function sasaranPuskesmasTerlayani(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $service = !empty($request->input('indicator')) ? Service::tryFrom($request->input('indicator')) : Service::KUNJUNGAN_ANC_6;
        if (!$service instanceof Service){
            return $this->response();
        }
        $tableName = $service->tableMaps();
        $tableColumn = $service->namaLembaga();
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');

        $results = DB::select("
    SELECT
        \"$tableColumn\" as name,
        COUNT(\"$tableColumn\") AS total
    FROM
        \"$tableName\"
    WHERE
        \"$tableColumn\" is not null
    GROUP BY
        \"$tableColumn\"
    ORDER BY
        \"$tableColumn\" ASC
");
        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => $item->name,
            ];
        }));
    }

    /**
     * @param DefaultRequest $request
     * @return Response
     */
    public function morbiditas(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $results = DB::select("SELECT
    nm_diagnosa as name,
    COUNT(nm_diagnosa) as total
FROM nd_diagnosa_ilp
GROUP BY nm_diagnosa");
        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => $item->name,
            ];
        }));
    }
}
