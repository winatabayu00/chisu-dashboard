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
        foreach ($target as $item) {
            $data[] = [
                'name' => $item->name,
                'people_count' => $item->jumlahPenduduk(),
                'service_count' => $item->totalKunjungan(),
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
// Payload processing

// Mengambil indicator dari payload, jika tidak ada default ke 'KUNJUNGAN_ANC_6'
        $service = !empty($request->input('indicator')) ? Service::tryFrom($request->input('indicator')) : Service::KUNJUNGAN_ANC_6;
        if (!$service instanceof Service) {
            return $this->response();
        }

// Mapping nama tabel berdasarkan indicator (target)
        $tableName = $service->tableMaps();

// Kolom sub_district berdasarkan indicator (target)
        $subDistrictColumn = $service->subDistrictColumn();

// Kolom tanggal berdasarkan indicator (target)
        $tableColumn = $service->dateColumn();

// Mengambil periode start dan end dari payload
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');

// Mengambil tipe periode (monthly, weekly, yearly), default adalah 'monthly'
        $periodType = !empty($request->input('period.type')) ? $request->input('period.type') : 'monthly';

// Mengambil informasi region dari payload
        $district = $request->input('region.district');
        $subDistrict = $request->input('region.sub_district');
        $healthCenter = $request->input('region.health_center');

// Jika health_center ada, maka hanya query pada puskesmas/health_center tersebut
        if (in_array($subDistrictColumn, ['Puskesmas', 'NAMA FASYANKES'])) {
            if (!empty($healthCenter)) {
                $subDistricts = [$healthCenter];
            } else {
                $subDistricts = [];
            }
        } elseif (!empty($subDistrictColumn)) {
            // Jika health_center tidak ada, ambil sub_districts berdasarkan district dan sub_district dari payload
            $subDistricts = $service->subDistricts($district, $subDistrict, $healthCenter);
        } else {
            $subDistricts = [];
        }

// Mengambil gender dari payload (misal 'male', 'female')
        $gender = $request->input('gender');

// Mengambil target dari payload (misal 'ibu_hamil', 'anak')
        $target = $request->input('target');

// Mengambil jenis agregasi dari payload (absolute, cumulative, dsb)
        $aggregateType = $request->input('aggregate');

// Mendapatkan tipe kolom (date atau character varying) dari tabel
        $columnType = DB::selectOne("
    SELECT data_type
    FROM information_schema.columns
    WHERE table_name = :table_name AND column_name = :column_name
", [
            'table_name' => $tableName,
            'column_name' => $tableColumn
        ])->data_type;

// Query preparation
        $query = "
    SELECT
        COUNT(\"$tableColumn\") AS count_anc,
";

        if ($periodType == 'weekly') {
            // Handle weekly aggregation
        } elseif ($periodType == 'yearly') {
            if ($columnType == 'character varying') {
                $query .= "TO_DATE(\"$tableColumn\", 'YYYY') AS year ";
            } else {
                $query .= "DATE_TRUNC('year', \"$tableColumn\") AS year ";
            }
        } else {
            if ($columnType == 'character varying') {
                $query .= "TO_DATE(\"$tableColumn\", 'YYYY-MM') AS month ";
            } else {
                $query .= "DATE_TRUNC('month', \"$tableColumn\") AS month ";
            }
        }

        $query .= "
    FROM
        \"$tableName\"
    WHERE 1=1
";

// Filtering based on startDate and endDate
        if (!is_null($startDate)) {
            $query .= " AND \"$tableColumn\" >= :start_date";
        }
        if (!is_null($endDate)) {
            $query .= " AND \"$tableColumn\" <= :end_date";
        }

// Filtering based on subDistricts
        if ($subDistrictColumn) {
            if (!empty($subDistricts)) {
                $query .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $subDistricts) . "')";
            } else {
                $query .= " AND \"$subDistrictColumn\" IS NOT NULL AND \"$subDistrictColumn\" <> ''";
            }
        }

// Filtering based on gender
//        if (!empty($gender)) {
//            $query .= " AND \"gender\" = :gender";
//        }

// Filtering based on target
//        if (!empty($target)) {
//            $query .= " AND \"target\" = :target";
//        }

// Finalize query
        if ($periodType == "weekly") {
            $query .= " GROUP BY week ORDER BY week ASC";
        } elseif ($periodType == "yearly") {
            $query .= " GROUP BY year ORDER BY year ASC";
        } else {
            $query .= " GROUP BY month ORDER BY month ASC";
        }

// Preparing parameters for query
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate,
//            'gender' => $gender,
//            'target' => $target,
        ];

// Execute query
        $results = DB::select($query, $params);

// Handle aggregate (absolute or cumulative)
        if ($aggregateType == 'absolute') {
            return $this->response(collect($results)->map(function ($item) {
                return [
                    'count' => $item->count_anc,
                    'name' => Carbon::parse($item->month)->format('F'),
                ];
            }));
        }

        $data = [];
        $count = 0;
        foreach (collect($results) as $item) {
            $count += $item->count_anc;
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
        $periodType = !empty($request->input('period.type')) ? $request->input('period.type') : 'monthly';


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
        $results = DB::select("SELECT nm_diagnosa        as name,
       COUNT(nm_diagnosa) as total
FROM nd_diagnosa_ilp
GROUP BY nm_diagnosa
ORDER BY total DESC
LIMIT 30");
        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => $item->name,
            ];
        }));
    }
}
