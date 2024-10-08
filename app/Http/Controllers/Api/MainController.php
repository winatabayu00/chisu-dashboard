<?php

namespace App\Http\Controllers\Api;

use App\Enums\Service;
use App\Enums\Target;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\DefaultRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
    public function listKunjungan(Request $request): Response
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'string'],
            'region_id' => ['nullable', 'string'],
            'region_type' => ['nullable', Rule::in(['kecamatan', 'puskesmas', 'kelurahan'])],
            'target' => ['nullable', 'string'],
            'aggregate' => ['nullable', Rule::in(['absolute', 'cumulative', 'percentage'])],
        ]);

        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');
        $district = $request->input('region.district');
        $subDistrict = $request->input('region.sub_district');
        $healthCenter = $request->input('region.health_center');

        if (empty($validated['tahun'])) {
            $s = $startDate ? date('Y', strtotime($startDate)) : null;
            $e = $endDate ? date('Y', strtotime($endDate)) : null;
            if ($s && !$e)
                $validated['tahun'] = $s;
            elseif ($e && !$s || $e != $s)
                $validated['tahun'] = $e;
            else
                $validated['tahun'] = date('Y');
        }
        if (empty($validated['target']))
            $validated['target'] = null;
        if (empty($validated['region_id']) || empty($validated['region_type'])) {
            if ($district) {
                $validated['region_id'] = $district;
                $validated['region_type'] = 'kecamatan';
            }elseif ($healthCenter) {
                $validated['region_id'] = $healthCenter;
                $validated['region_type'] = 'puskesmas';
            }elseif ($subDistrict) {
                $validated['region_id'] = $subDistrict;
                $validated['region_type'] = 'kelurahan';
            }else {
                $validated['region_id'] = null;
                $validated['region_type'] = null;
            }
        }
        if (empty($validated['aggregate']))
            $validated['aggregate'] = 'absolute';
        
        $target = Target::tryFrom($validated['target']) ?? null;

        $params = [
            'tahun' => intval($validated['tahun'])
        ];
        $query = "SELECT jenis, sum(lakilaki) AS lakilaki, sum(perempuan) AS perempuan, sum(lakilaki+perempuan) AS total";
        $query .= " FROM data_sasaran WHERE tahun = :tahun";
        if (!empty($validated['target']) && $target) {
            $params['jenis'] = $target->capitalName(); //$validated['target'];
            $query .= " AND jenis = :jenis";
        }
        if (!empty($validated['region_id'])) {
            // $query .= ", " . $validated['region_type'];
            if ($validated['region_type'] == 'kecamatan') {
                $region = collect(SelectOptionController::SUB_DISTRICT)
                    ->where('kecamatan', '=', $validated['region_id'])->map(function($item) {
                        return $item['name'];
                    })->toArray();
            }elseif ($validated['region_type'] == 'puskesmas') {
                $region = collect(SelectOptionController::SUB_DISTRICT)
                    ->where('puskesmas', '=', $validated['region_id'])->map(function($item) {
                        return $item['name'];
                    })->toArray();
            }else {
                $region = [$validated['region_id']];
            }

            $query .= " AND kelurahan IN('" . implode("', '", $region) . "')";
        }else {
            $region = [];
        }

        $query .= " GROUP BY jenis";

        $results = DB::select($query, $params);
        // return $this->response($results);

        $params = [
            'tahun' => intval($validated['tahun'])
        ];
        $queries = [];
        foreach ($results as $item) {
            switch ($item->jenis) {
                case 'IBU HAMIL':
                    $service = Service::KUNJUNGAN_ANC_6;
                    $tableName = $service->tableMaps();
                    $dateColumn = $service->dateColumn();
                    $q = "SELECT 'IBU HAMIL' jenis, DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                    }
                    if (!empty($region)) {
                        $subDistrictColumn = $service->subDistrictColumn();
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'IBU BERSALIN':
                    $service = Service::PERSALINAN_DI_FASILITAS_KESEHATAN;
                    $tableName = $service->tableMaps();
                    $dateColumn = $service->dateColumn();
                    $q = "SELECT 'IBU BERSALIN' jenis, DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                    }
                    if (!empty($region)) {
                        $subDistrictColumn = $service->subDistrictColumn();
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'BAYI BARU LAHIR':
                    # code...
                    break;
                case 'BAYI 0-11 BULAN':
                    $service = Service::IMUNISASI_DASAR_LENGKAP;
                    $tableName = $service->tableMaps();
                    $dateColumn = $service->dateColumn();
                    $q = "SELECT 'BAYI 0-11 BULAN' jenis, DATE_PART('year', \"$dateColumn\") tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', \"$dateColumn\") = :tahun";
                    }
                    if (!empty($region)) {
                        $subDistrictColumn = $service->subDistrictColumn();
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'BAYI 12-23 BULAN':
                    // $service = Service::IMUNISASI_DASAR_LENGKAP;
                    $tableName = 'baduta';
                    $dateColumn = 'Tanggal Imunisasi DPT-Hb-Hib 4';
                    $q = "SELECT 'BAYI 12-23 BULAN' jenis, DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                    }
                    if (!empty($region)) {
                        $subDistrictColumn = 'Kelurahan atau Desa';
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'BALITA':
                    // $service = Service::IMUNISASI_DASAR_LENGKAP;
                    $tableName = 'eppbgm';
                    $dateColumn = 'Tanggal Pengukuran';
                    $q = "SELECT 'BALITA' jenis, DATE_PART('year', \"$dateColumn\") tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', \"$dateColumn\") = (:tahun - 1)";
                    }
                    if (!empty($region)) {
                        $subDistrictColumn = 'Desa/Kel';
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'USIA PENDIDIKAN DASAR':
                    $service = Service::SKRINING_KESEHATAN;
                    $tableName = $service->tableMaps();
                    $dateColumn = $service->dateColumn();
                    $q = "SELECT 'USIA PENDIDIKAN DASAR' jenis, DATE_PART('year', \"$dateColumn\") tahun, COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                    if ($dateColumn) {
                        $q .= " AND DATE_PART('year', \"$dateColumn\") = :tahun";
                    }
                    if (!empty($validated['region_type']) && $validated['region_type'] == 'puskesmas') {
                        $subDistrictColumn = $service->subDistrictColumn();
                        $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", [$validated['region_id']]) . "')";
                    }
                    $q .= " group by tahun";
                    $queries[] = $q;
                    break;
                case 'USIA PRODUKTIF':
                    # code...
                    break;
            }
        }

        if (empty($queries))
            return $this->response([]);

        $query = "SELECT * FROM (" . implode("\nUNION\n", $queries) . ") AS T";
        // return $this->response(['query' => $query]);
        
        $results2 = DB::select($query, $params);

        $data = [];
        $all = [];
        foreach ($results as $item) {
            if ($item->jenis == 'PROYEKSI JUMLAH PENDUDUK') {
                $all = [
                    'target' => $item->total,
                    'service' => $item->total,
                ];
            }else {
                $d = [
                    'name' => $item->jenis,
                    'target_total' => $item->total,
                    'target_lakilaki' => $item->lakilaki,
                    'target_perempuan' => $item->perempuan,
                ];

                foreach ($results2 as $item2) {
                    if ($item->jenis == $item2->jenis) {
                        $d['service_total'] = $item2->total;
                        $d['service_lakilaki'] = $item2->lakilaki;
                        $d['service_perempuan'] = $item2->perempuan;
                    }
                }

                $data[] = $d;
            }
        }
        
        $data = $this->finalProcess($data, $validated['aggregate'], ['target_total', 'target_lakilaki', 'target_perempuan', 'service_total', 'service_lakilaki', 'service_perempuan']);
        return $this->response([ 'results' => $data, 'total' => $all]);
    }

    /**
     * @return Response
     */
    public function totalTerlayani(Request $request): Response
    {
        $validated = $request->validate([
            'tahun' => ['nullable', 'string'],
            'region_id' => ['nullable', 'string'],
            'region_type' => ['nullable', Rule::in(['kecamatan', 'puskesmas', 'kelurahan'])],
            'target' => ['nullable', 'string'],
            'aggregate' => ['nullable', Rule::in(['absolute', 'cumulative', 'percentage'])],
        ]);

        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');
        $district = $request->input('region.district');
        $subDistrict = $request->input('region.sub_district');
        $healthCenter = $request->input('region.health_center');

        if (empty($validated['tahun'])) {
            $s = $startDate ? date('Y', strtotime($startDate)) : null;
            $e = $endDate ? date('Y', strtotime($endDate)) : null;
            if ($s && !$e)
                $validated['tahun'] = $s;
            elseif ($e && !$s || $e != $s)
                $validated['tahun'] = $e;
            else
                $validated['tahun'] = date('Y');
        }
        if (empty($validated['tahun']))
            $validated['tahun'] = date('Y');
        if (empty($validated['target'])) {
            return $this->response([]);
            // $validated['target'] = null;
        }
        if (empty($validated['region_id']) || empty($validated['region_type'])) {
            if ($district) {
                $validated['region_id'] = $district;
                $validated['region_type'] = 'kecamatan';
            }elseif ($healthCenter) {
                $validated['region_id'] = $healthCenter;
                $validated['region_type'] = 'puskesmas';
            }elseif ($subDistrict) {
                $validated['region_id'] = $subDistrict;
                $validated['region_type'] = 'kelurahan';
            }else {
                $validated['region_id'] = null;
                $validated['region_type'] = null;
            }
        }
        if (empty($validated['aggregate']))
        $validated['aggregate'] = 'absolute';

        if (!empty($validated['region_id'])) {
            // $query .= ", " . $validated['region_type'];
            if ($validated['region_type'] == 'kecamatan') {
                $region = collect(SelectOptionController::SUB_DISTRICT)
                    ->where('kecamatan', '=', $validated['region_id'])->map(function($item) {
                        return $item['name'];
                    })->toArray();
            }elseif ($validated['region_type'] == 'puskesmas') {
                $region = collect(SelectOptionController::SUB_DISTRICT)
                    ->where('puskesmas', '=', $validated['region_id'])->map(function($item) {
                        return $item['name'];
                    })->toArray();
            }else {
                $region = [$validated['region_id']];
            }
        }else {
            $region = [];
        }

        $params = [
            'tahun' => intval($validated['tahun'])
        ];

        $target = Target::tryFrom($validated['target']) ?? Target::REMAJA_PUTRI;
        switch ($target) {
            case Target::IBU_HAMIL:
                $service = Service::KUNJUNGAN_ANC_6;
                $tableName = $service->tableMaps();
                $dateColumn = $service->dateColumn();
                $q = "SELECT 'IBU HAMIL' jenis, TO_DATE(\"$dateColumn\", 'YYYY-MM') \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                }
                if (!empty($region)) {
                    $subDistrictColumn = $service->subDistrictColumn();
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                }
                $q .= " group by month";
                break;
            case Target::IBU_BERSALIN:
                $service = Service::PERSALINAN_DI_FASILITAS_KESEHATAN;
                $tableName = $service->tableMaps();
                $dateColumn = $service->dateColumn();
                $q = "SELECT 'IBU BERSALIN' jenis, TO_DATE(\"$dateColumn\", 'YYYY-MM') \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                }
                if (!empty($region)) {
                    $subDistrictColumn = $service->subDistrictColumn();
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                }
                $q .= " group by month";
                break;
            case Target::BAYI_BARU_LAHIR:
                # code...
                break;
            case Target::BAYI:
                $service = Service::IMUNISASI_DASAR_LENGKAP;
                $tableName = $service->tableMaps();
                $dateColumn = $service->dateColumn();
                $q = "SELECT 'BAYI 0-11 BULAN' jenis, DATE_TRUNC('month', \"$dateColumn\") \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('month', \"$dateColumn\") = :tahun";
                }
                if (!empty($region)) {
                    $subDistrictColumn = $service->subDistrictColumn();
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                }
                $q .= " group by month";
                break;
            case Target::ANAK_USIA_12_SAMPAI_23_BULAN:
                // $service = Service::IMUNISASI_DASAR_LENGKAP;
                $tableName = 'baduta';
                $dateColumn = 'Tanggal Imunisasi DPT-Hb-Hib 4';
                $q = "SELECT 'BAYI 12-23 BULAN' jenis, TO_DATE(\"$dateColumn\", 'YYYY-MM') \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('year', TO_DATE(\"$dateColumn\", 'YYYY')) = :tahun";
                }
                if (!empty($region)) {
                    $subDistrictColumn = 'Kelurahan atau Desa';
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                }
                $q .= " group by month";
                break;
            case Target::BALITA:
                // $service = Service::IMUNISASI_DASAR_LENGKAP;
                $tableName = 'eppbgm';
                $dateColumn = 'Tanggal Pengukuran';
                $q = "SELECT 'BALITA' jenis, DATE_TRUNC('month', \"$dateColumn\") \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('year', \"$dateColumn\") = (:tahun - 1)";
                }
                if (!empty($region)) {
                    $subDistrictColumn = 'Desa/Kel';
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", $region) . "')";
                }
                $q .= " group by month";
                break;
            case Target::ANAK_USIA_SEKOLAH:
                $service = Service::SKRINING_KESEHATAN;
                $tableName = $service->tableMaps();
                $dateColumn = $service->dateColumn();
                $q = "SELECT 'USIA PENDIDIKAN DASAR' jenis, DATE_TRUNC('month', \"$dateColumn\") \"month\", COUNT(*) total, COUNT(*) perempuan, 0 lakilaki from \"$tableName\" where true";
                if ($dateColumn) {
                    $q .= " AND DATE_PART('year', \"$dateColumn\") = :tahun";
                }
                if (!empty($validated['region_type']) && $validated['region_type'] == 'puskesmas') {
                    $subDistrictColumn = $service->subDistrictColumn();
                    $q .= " AND \"$subDistrictColumn\" IN('" . implode("', '", [$validated['region_id']]) . "')";
                }
                $q .= " group by month";
                break;
            case Target::REMAJA_PUTRI:
                # code...
                break;
        }

        if (isset($q))
            $results = DB::select($q, ['tahun' => intval($validated['tahun'])]);
        else
            $results = [];

        $data = collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => Carbon::parse($item->month)->format('F'),
            ];
        })->toArray();

        $data = $this->finalProcess($data, $validated['aggregate']);
        return $this->response($data);
    }

    /**
     * @return Response
     */
    public function summaryKunjungan(Request $request): Response
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
        // Mengambil indicator dari payload, jika tidak ada default ke 'KUNJUNGAN_ANC_6'
        $service = !empty($request->input('indicator')) ? Service::tryFrom($request->input('indicator')) : Service::PASIEN_HIPERTENSI;
        if (!$service instanceof Service) {
            return $this->response([]);
        }

        // Mapping nama tabel berdasarkan indicator (target)
        $tableName = $service->tableMaps();

        if (strpos($tableName, 'function:') === 0) {
            $func = str_replace('function:', '', $tableName) . 'Terlayani';
            return $this->$func($request, $service);
        }

        // Kolom sub_district berdasarkan indicator (target)
        $subDistrictColumn = $service->subDistrictColumn();

        // Kolom tanggal berdasarkan indicator (target)
        $tableColumn = $service->dateColumn();
        if (empty($tableName) || empty($tableColumn)) {
            return $this->response([]);
        }

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
        if (in_array($subDistrictColumn, ['puskesmas', 'NAMA FASYANKES'])) {
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
        COUNT(\"$tableColumn\") AS count_anc,";

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

        $params = [];
        // Filtering based on startDate and endDate
        if (!empty($startDate)) {
            $query .= " AND \"$tableColumn\" >= :start_date";
            $params['start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $query .= " AND \"$tableColumn\" <= :end_date";
            $params['end_date'] = $endDate;
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
        if (!empty($gender)) {
            $query .= " AND \"gender\" = :gender";
            // $params['gender'] = $gender;
        }

        // Filtering based on target
        // if (!empty($target)) {
        //     $query .= " AND \"target\" = :target";
        //     // $params['target'] = $target;
        // }

        // Finalize query
        if ($periodType == "weekly") {
            $query .= " GROUP BY week ORDER BY week ASC";
        } elseif ($periodType == "yearly") {
            $query .= " GROUP BY year ORDER BY year ASC";
        } else {
            $query .= " GROUP BY month ORDER BY month ASC";
        }

        // Execute query
        $results = DB::select($query, $params);

        $data = collect($results)->map(function ($item) {
            return [
                'count' => $item->count_anc,
                'name' => Carbon::parse($item->month)->format('F'),
            ];
        })->toArray();

        $data = $this->finalProcess($data, $aggregateType);
        return $this->response($data);
    }

    /**
     * @param DefaultRequest $request
     * @return Response
     */
    public function sasaranPuskesmasTerlayani(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $service = !empty($request->input('indicator')) ? Service::tryFrom($request->input('indicator')) : Service::PASIEN_HIPERTENSI;
        if (!$service instanceof Service){
            return $this->response([]);
        }
        $tableName = $service->tableMaps();

        if (strpos($tableName, 'function:') === 0) {
            $func = str_replace('function:', '', $tableName) . 'PuskesmasTerlayani';
            return $this->$func($request, $service);
        }

        $tableColumn = $service->namaLembaga();
        if (empty($tableName) || empty($tableColumn)) {
            return $this->response([]);
        }
        $dateColumn = $service->dateColumn();
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');
        // $periodType = !empty($request->input('period.type')) ? $request->input('period.type') : 'monthly';
        $aggregateType = $request->input('aggregate');
        if ($aggregateType == 'cumulative')
            $aggregateType = null;

        $params = [];
        $query = "
            SELECT
                \"$tableColumn\" as name,
                COUNT(\"$tableColumn\") AS total
            FROM
                \"$tableName\"
            WHERE
                \"$tableColumn\" is not null";
        
        // Filtering based on startDate and endDate
        if (!empty($startDate) && !empty($dateColumn)) {
            $query .= " AND \"$dateColumn\" >= :start_date";
            $params['start_date'] = $startDate;
        }
        if (!empty($endDate) && !empty($dateColumn)) {
            $query .= " AND \"$dateColumn\" <= :end_date";
            $params['end_date'] = $endDate;
        }

        $query .= " GROUP BY
                    \"$tableColumn\"
                ORDER BY
                    \"$tableColumn\" ASC
            ";
        $results = DB::select($query, $params);
        $data = collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => $item->name,
            ];
        })->toArray();

        $data = $this->finalProcess($data, $aggregateType);
        return $this->response($data);

        // return $this->response(collect($results)->map(function ($item) {
        //     return [
        //         'count' => $item->total,
        //         'name' => $item->name,
        //     ];
        // }));
    }

    /**
     * @param DefaultRequest $request
     * @return Response
     */
    public function morbiditas(DefaultRequest $request): \Winata\Core\Response\Http\Response
    {
        $dummyService = Service::PASIEN_HIPERTENSI;
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $district = $request->input('region.district');
        $subDistrict = $request->input('region.sub_district');
        $healthCenter = $request->input('region.health_center');
        $subDistricts = $dummyService->subDistricts($district, $subDistrict, $healthCenter);

        $params = [];
        $query = "SELECT nm_diagnosa as name, COUNT(nm_diagnosa) as total FROM nd_diagnosa_ilp WHERE true ";
        // Filtering based on startDate and endDate
        if (!empty($startDate)) {
            $query .= " AND \"tgl\" >= :start_date";
            $params['start_date'] = $startDate;
        }
        if (!empty($endDate)) {
            $query .= " AND \"tgl\" <= :end_date";
            $params['end_date'] = $endDate;
        }

        // Filtering based on gender
        if (!empty($gender)) {
            $gender = $gender == 'L' ? 'male' : ($gender == 'P' ? 'female' : null);
            if ($gender) {
                $query .= " AND \"sex\" = :gender";
                $params['gender'] = $gender;
            }
        }

        if (!empty($subDistricts)) {
            $query .= " AND \"nama_kelurahan\" IN('" . implode("', '", $subDistricts) . "')";
        }

        $query .= " GROUP BY nm_diagnosa ORDER BY total DESC LIMIT 30";

        $results = DB::select($query, $params);
        return $this->response(collect($results)->map(function ($item) {
            return [
                'count' => $item->total,
                'name' => $item->name,
            ];
        }));
    }

    private function ambilInputSasaran(DefaultRequest $request, Service $service) : array {
        $startDate = $request->input('period.start');
        $endDate = $request->input('period.end');

        // Mengambil tipe periode (monthly, weekly, yearly), default adalah 'monthly'
        $periodType = !empty($request->input('period.type')) ? $request->input('period.type') : 'monthly';

        // Mengambil informasi region dari payload
        $district = $request->input('region.district');
        $subDistrict = $request->input('region.sub_district');
        $healthCenter = $request->input('region.health_center');
        $subDistricts = $service->subDistricts($district, $subDistrict, $healthCenter);
        $districtData = collect(SelectOptionController::DISTRICT)
            ->where('kode', '=', $district)->first();
        $districtName = !empty($districtData) ? $districtData['name'] : null;

        // Mengambil gender dari payload (misal 'male', 'female')
        $gender = $request->input('gender');

        // Mengambil target dari payload (misal 'ibu_hamil', 'anak')
        $target = $request->input('target');

        // Mengambil jenis agregasi dari payload (absolute, cumulative, dsb)
        $aggregateType = $request->input('aggregate');

        return [
            $startDate,
            $endDate,
            $periodType, // unused, 'monthly' supprted only
            $district,
            $districtName,
            $healthCenter,
            $subDistricts,
            $gender,
            $target, // unused
            $aggregateType
        ];
    }
    protected function sasaranHipertensiTerlayani(DefaultRequest $request, Service $service) : Response {
        list (
            $startDate,
            $endDate,
            $periodType,
            $district,
            $districtName,
            $healthCenter,
            $subDistricts,
            $gender,
            $target,
            $aggregateType
        ) = $this->ambilInputSasaran($request, $service);

        if ($districtName)
            $query = ['nama_kecamatan' => $districtName];
        else
            $query = [];
        
        // get data dari https://gayatri.mojokertokota.go.id/dbilp/htn/htperbulan.php
        $response = Http::get('https://gayatri.mojokertokota.go.id/dbilp/htn/htperbulan.php', $query);
        $data = [];
        if ($response->ok()) {
            $resp = @json_decode($response->body(), true);
            $data = [];
            usort($resp['data'], function($a, $b) {
                $ta = strtotime(Carbon::parse($a['tahun'] . '-' . str_pad($a['bulan'], 2, '0', STR_PAD_LEFT) . '-01'));
                $tb = strtotime(Carbon::parse($b['tahun'] . '-' . str_pad($b['bulan'], 2, '0', STR_PAD_LEFT) . '-01'));
                if ($ta == $tb) return 0;
                return $ta < $tb ? -1 : 1;
            });

            $data = collect($resp['data'])->map(function($item) {
                return [
                    'count' => $item['jumlah'],
                    'name' => Carbon::parse($item['tahun'] . '-' . str_pad($item['bulan'], 2, '0', STR_PAD_LEFT) . '-01')->format('F'),
                ];
            })->toArray();
        }

        return $this->response($data);
    }

    protected function sasaranHipertensiPuskesmasTerlayani(DefaultRequest $request, Service $service) : Response {
        list (
            $startDate,
            $endDate,
            $periodType,
            $district,
            $districtName,
            $healthCenter,
            $subDistricts,
            $gender,
            $target,
            $aggregateType
        ) = $this->ambilInputSasaran($request, $service);

        if ($districtName)
            $query = ['nama_kecamatan' => $districtName];
        else
            $query = [];

        // get data dari https://gayatri.mojokertokota.go.id/dbilp/htn/htperfaskes.php
        $response = Http::get('https://gayatri.mojokertokota.go.id/dbilp/htn/htperfaskes.php', $query);
        $data = [];
        if ($response->ok()) {
            $resp = @json_decode($response->body(), true);
            $data = collect($resp['data'])->map(function($item) {
                return [
                    'count' => $item['jumlah'],
                    'name' => $item['fasyankes'],
                ];
            })->toArray();
        }


        return $this->response($data);
    }

    private function finalProcess($data, $aggregate, $columns = ['count']) {
        if ($aggregate == 'cumulative') {
            $total = [];
            foreach ($columns as $col) {
                $total[$col] = 0;
            }
            for ($i=0; $i < count($data); $i++) {
                foreach ($columns as $col) {
                    $count = $data[$i][$col];
                    $data[$i][$col] = $count + $total[$col];
                    $total[$col] += $count;
                }
            }
        }elseif ($aggregate == 'percentage') {
            $total = [];
            foreach ($columns as $col) {
                $total[$col] = 0;
            }
            for ($i=0; $i < count($data); $i++) {
                foreach ($columns as $col) {
                    $total[$col] += $data[$i][$col];
                }
            }

            for ($i=0; $i < count($data); $i++) {
                foreach ($columns as $col) {
                    $data[$i][$col] = $total[$col] > 0 ? $data[$i][$col] * 100 / $total[$col] : 0;
                }
            }
        }

        return $data;
    }
}
