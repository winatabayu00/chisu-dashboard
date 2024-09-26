<?php

namespace App\Http\Controllers\Api;

use App\Enums\Service;
use App\Http\Controllers\Controller;
use App\Http\Requests\DefaultRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * @param DefaultRequest $request
     * @return Response
     */
    public function sasaranTerlayani(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $service = Service::IMUNISASI_LANJUTAN_BADUTA_LENGKAP;
        $tableName = $service->tableMaps();
        $tableColumn = $service->dateColumn();
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');

        $columnType = DB::selectOne("
    SELECT data_type
    FROM information_schema.columns
    WHERE table_name = :table_name AND column_name = :column_name
    ORDER BY :column_name DESC
", [
            'table_name' => $tableName,
            'column_name' => $tableColumn
        ])->data_type;

        $query = "
    SELECT
        COUNT(\"$tableColumn\") AS count_anc,
";

        if ($columnType == 'character varying') {
            $query .= "DATE_TRUNC('month', TO_DATE(\"$tableColumn\", 'YYYY-MM-DD')) AS month ";
        } else {
            $query .= "DATE_TRUNC('month', \"$tableColumn\") AS month ";
        }

        $query .= "
    FROM
        \"$tableName\"
    WHERE 1=1
";

// Menambahkan kondisi untuk startDate jika ada
        if (!is_null($startDate)) {
            $query .= " AND \"$tableColumn\" >= :start_date";
        }

// Menambahkan kondisi untuk endDate jika ada
        if (!is_null($endDate)) {
            $query .= " AND \"$tableColumn\" <= :end_date";
        }

        $query .= "
    GROUP BY
        month
";

// Parameter binding
        $params = [];

        if (!is_null($startDate)) {
            $params['start_date'] = $startDate;
        }

        if (!is_null($endDate)) {
            $params['end_date'] = $endDate;
        }

        $results = DB::select($query, $params);

        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->count_anc,
                'month' => Carbon::parse($item->month)->format('F'),
            ];
        }));
    }

    /**
     * @return Response
     */
    public function sasaranPuskesmasTerlayani(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $year = 2023; // Ganti dengan tahun yang diinginkan

        $results = DB::select("
    SELECT
        \"Nama Lembaga\" as name,
        COUNT(\"Nama Lembaga\") AS count_anc
    FROM
        \"dbEkohortAnc\"
    WHERE
        EXTRACT(YEAR FROM TO_DATE(\"Tanggal Anc\", 'YYYY-MM-DD')) = ?
    GROUP BY
        \"Nama Lembaga\"
    ORDER BY
        count_anc ASC
", [$year]);
        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->count_anc,
                'name' => $item->name,
            ];
        }));
    }
}
