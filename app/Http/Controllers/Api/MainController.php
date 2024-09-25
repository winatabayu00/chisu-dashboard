<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function sasaranTerlayani(): \Winata\Core\Response\Http\Response
    {
        $year = 2023; // Ganti dengan tahun yang diinginkan

        $results = DB::select("
    SELECT
        COUNT(\"Tanggal Anc\") AS count_anc,
        DATE_TRUNC('month', TO_DATE(\"Tanggal Anc\", 'YYYY-MM-DD')) AS month
    FROM
        \"dbEkohortAnc\"
    WHERE
        EXTRACT(YEAR FROM TO_DATE(\"Tanggal Anc\", 'YYYY-MM-DD')) = ?
    GROUP BY
        month
    ORDER BY
        month
", [$year]);
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
    public function sasaranPuskesmasTerlayani(): \Winata\Core\Response\Http\Response
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
