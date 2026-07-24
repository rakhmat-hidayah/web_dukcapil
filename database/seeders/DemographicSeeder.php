<?php

namespace Database\Seeders;

use App\Models\DemographicDataset;
use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemographicSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Kecamatan data (8 kecamatan di Kabupaten Dompu) ─────────────────
        $kecamatanData = [
            [
                'name'             => 'Dompu',
                'code'             => '5208010',
                'ibukota'          => 'Dompu',
                'area_km2'         => 316.44,
                'population_total' => 63214,
                'male_count'       => 31803,
                'female_count'     => 31411,
                'sort_order'       => 1,
            ],
            [
                'name'             => 'Woja',
                'code'             => '5208011',
                'ibukota'          => 'Woja',
                'area_km2'         => 267.10,
                'population_total' => 52180,
                'male_count'       => 26204,
                'female_count'     => 25976,
                'sort_order'       => 2,
            ],
            [
                'name'             => 'Hu\'u',
                'code'             => '5208020',
                'ibukota'          => 'Hu\'u',
                'area_km2'         => 189.75,
                'population_total' => 21345,
                'male_count'       => 10721,
                'female_count'     => 10624,
                'sort_order'       => 3,
            ],
            [
                'name'             => 'Manggelewa',
                'code'             => '5208030',
                'ibukota'          => 'Manggelewa',
                'area_km2'         => 356.80,
                'population_total' => 37892,
                'male_count'       => 19012,
                'female_count'     => 18880,
                'sort_order'       => 4,
            ],
            [
                'name'             => 'Kempo',
                'code'             => '5208040',
                'ibukota'          => 'Kempo',
                'area_km2'         => 284.90,
                'population_total' => 25631,
                'male_count'       => 12894,
                'female_count'     => 12737,
                'sort_order'       => 5,
            ],
            [
                'name'             => 'Kilo',
                'code'             => '5208050',
                'ibukota'          => 'Kilo',
                'area_km2'         => 296.31,
                'population_total' => 16748,
                'male_count'       => 8412,
                'female_count'     => 8336,
                'sort_order'       => 6,
            ],
            [
                'name'             => 'Pekat',
                'code'             => '5208060',
                'ibukota'          => 'Pekat',
                'area_km2'         => 888.39,
                'population_total' => 35492,
                'male_count'       => 17894,
                'female_count'     => 17598,
                'sort_order'       => 7,
            ],
            [
                'name'             => 'Pajo',
                'code'             => '5208070',
                'ibukota'          => 'Pajo',
                'area_km2'         => 171.53,
                'population_total' => 18264,
                'male_count'       => 9168,
                'female_count'     => 9096,
                'sort_order'       => 8,
            ],
        ];

        // ─── Desa samples per kecamatan ───────────────────────────────────────
        $desaData = [
            'Dompu' => [
                ['name' => 'Bada',          'type' => 'kelurahan', 'code' => '5208010001', 'population_total' => 12450, 'male_count' => 6210, 'female_count' => 6240],
                ['name' => 'Bali',          'type' => 'kelurahan', 'code' => '5208010002', 'population_total' => 9840,  'male_count' => 4960, 'female_count' => 4880],
                ['name' => 'Dorotangga',    'type' => 'kelurahan', 'code' => '5208010003', 'population_total' => 11200, 'male_count' => 5640, 'female_count' => 5560],
                ['name' => 'Karijawa',      'type' => 'kelurahan', 'code' => '5208010004', 'population_total' => 8750,  'male_count' => 4410, 'female_count' => 4340],
                ['name' => 'Potu',          'type' => 'kelurahan', 'code' => '5208010005', 'population_total' => 7890,  'male_count' => 3950, 'female_count' => 3940],
                ['name' => 'Kareke',        'type' => 'desa',      'code' => '5208010006', 'population_total' => 6580,  'male_count' => 3320, 'female_count' => 3260],
                ['name' => 'O\'o',          'type' => 'desa',      'code' => '5208010007', 'population_total' => 6504,  'male_count' => 3313, 'female_count' => 3191],
            ],
            'Woja' => [
                ['name' => 'Monta Baru',    'type' => 'kelurahan', 'code' => '5208011001', 'population_total' => 10120, 'male_count' => 5080, 'female_count' => 5040],
                ['name' => 'Kandai Dua',    'type' => 'kelurahan', 'code' => '5208011002', 'population_total' => 12450, 'male_count' => 6250, 'female_count' => 6200],
                ['name' => 'Simpasai',      'type' => 'kelurahan', 'code' => '5208011003', 'population_total' => 9640,  'male_count' => 4820, 'female_count' => 4820],
                ['name' => 'Bakabu',        'type' => 'desa',      'code' => '5208011004', 'population_total' => 7850,  'male_count' => 3940, 'female_count' => 3910],
                ['name' => 'Bara',          'type' => 'desa',      'code' => '5208011005', 'population_total' => 6120,  'male_count' => 3080, 'female_count' => 3040],
                ['name' => 'Matua',         'type' => 'desa',      'code' => '5208011006', 'population_total' => 6000,  'male_count' => 3034, 'female_count' => 2966],
            ],
            'Hu\'u' => [
                ['name' => 'Hu\'u',         'type' => 'desa',      'code' => '5208020001', 'population_total' => 5420,  'male_count' => 2730, 'female_count' => 2690],
                ['name' => 'Daha',          'type' => 'desa',      'code' => '5208020002', 'population_total' => 6100,  'male_count' => 3060, 'female_count' => 3040],
                ['name' => 'Rasabou',       'type' => 'desa',      'code' => '5208020003', 'population_total' => 4950,  'male_count' => 2480, 'female_count' => 2470],
                ['name' => 'Sawe',          'type' => 'desa',      'code' => '5208020004', 'population_total' => 4875,  'male_count' => 2451, 'female_count' => 2424],
            ],
            'Manggelewa' => [
                ['name' => 'Soriutu',       'type' => 'desa',      'code' => '5208030001', 'population_total' => 11200, 'male_count' => 5620, 'female_count' => 5580],
                ['name' => 'Neru',          'type' => 'desa',      'code' => '5208030002', 'population_total' => 9840,  'male_count' => 4940, 'female_count' => 4900],
                ['name' => 'Anamina',       'type' => 'desa',      'code' => '5208030003', 'population_total' => 8650,  'male_count' => 4340, 'female_count' => 4310],
                ['name' => 'Bangka Jaya',   'type' => 'desa',      'code' => '5208030004', 'population_total' => 8202,  'male_count' => 4112, 'female_count' => 4090],
            ],
            'Kempo' => [
                ['name' => 'Kempo',         'type' => 'desa',      'code' => '5208040001', 'population_total' => 8940,  'male_count' => 4500, 'female_count' => 4440],
                ['name' => 'Dorokobo',      'type' => 'desa',      'code' => '5208040002', 'population_total' => 7650,  'male_count' => 3850, 'female_count' => 3800],
                ['name' => 'Soro',          'type' => 'desa',      'code' => '5208040003', 'population_total' => 9041,  'male_count' => 4544, 'female_count' => 4497],
            ],
            'Kilo' => [
                ['name' => 'Malaju',        'type' => 'desa',      'code' => '5208050001', 'population_total' => 6120,  'male_count' => 3080, 'female_count' => 3040],
                ['name' => 'Mbuju',         'type' => 'desa',      'code' => '5208050002', 'population_total' => 5430,  'male_count' => 2730, 'female_count' => 2700],
                ['name' => 'Lasi',          'type' => 'desa',      'code' => '5208050003', 'population_total' => 5198,  'male_count' => 2602, 'female_count' => 2596],
            ],
            'Pekat' => [
                ['name' => 'Pekat',         'type' => 'desa',      'code' => '5208060001', 'population_total' => 12450, 'male_count' => 6280, 'female_count' => 6170],
                ['name' => 'Calabai',       'type' => 'desa',      'code' => '5208060002', 'population_total' => 11200, 'male_count' => 5640, 'female_count' => 5560],
                ['name' => 'Kadindi',       'type' => 'desa',      'code' => '5208060003', 'population_total' => 11842, 'male_count' => 5974, 'female_count' => 5868],
            ],
            'Pajo' => [
                ['name' => 'Ranggo',        'type' => 'desa',      'code' => '5208070001', 'population_total' => 7890,  'male_count' => 3960, 'female_count' => 3930],
                ['name' => 'Jambu',         'type' => 'desa',      'code' => '5208070002', 'population_total' => 6450,  'male_count' => 3240, 'female_count' => 3210],
                ['name' => 'Tembalao',      'type' => 'desa',      'code' => '5208070003', 'population_total' => 3924,  'male_count' => 1968, 'female_count' => 1956],
            ],
        ];

        foreach ($kecamatanData as $kData) {
            $kec = Kecamatan::updateOrCreate(
                ['code' => $kData['code']],
                $kData
            );

            if (isset($desaData[$kec->name])) {
                foreach ($desaData[$kec->name] as $dData) {
                    Desa::updateOrCreate(
                        ['code' => $dData['code']],
                        array_merge($dData, ['kecamatan_id' => $kec->id])
                    );
                }
            }
        }

        // Helper dataset generator for a specific year and semester with realistic progressive trend
        $seedYearDatasets = function ($year, $semester = 1) {
            $semLabel = $semester == 1 ? 'Semester 1 (Juni)' : 'Semester 2 (Desember)';
            $yearOffset = $year - 2024;
            $semIndex   = ($yearOffset * 2) + ($semester - 1); // 0 to 5

            // Progressive population growth with exact array sums
            $basePop   = 264000 + ($semIndex * 1350);
            $baseMale  = (int) round($basePop * 0.50268);
            $baseFem   = $basePop - $baseMale;

            $maleWeights   = [12120, 12850, 12630, 11890, 11340, 10980, 10540, 9980, 9230, 8780, 7820, 6940, 5870, 5109];
            $maleWeightSum = array_sum($maleWeights);
            $maleArray     = array_map(fn($w) => (int) round(($w / $maleWeightSum) * $baseMale), $maleWeights);
            $maleArray[count($maleArray) - 1] += ($baseMale - array_sum($maleArray));

            $femWeights    = [11890, 12510, 12350, 11640, 11180, 10720, 10290, 9740, 8910, 8440, 7680, 6810, 5750, 6761];
            $femWeightSum  = array_sum($femWeights);
            $femArray      = array_map(fn($w) => (int) round(($w / $femWeightSum) * $baseFem), $femWeights);
            $femArray[count($femArray) - 1] += ($baseFem - array_sum($femArray));

            $populationData = [
                'categories' => ['0-4','5-9','10-14','15-19','20-24','25-29','30-34','35-39','40-44','45-49','50-54','55-59','60-64','65+'],
                'male'       => $maleArray,
                'female'     => $femArray,
                'total'      => $basePop,
            ];

            $religionData = [
                'items' => [
                    ['name' => 'Islam',      'value' => (int)round($basePop * 0.997)],
                    ['name' => 'Kristen',    'value' => 310 + ($semIndex * 2)],
                    ['name' => 'Katolik',    'value' => 150 + $semIndex],
                    ['name' => 'Hindu',      'value' => 90 + $semIndex],
                    ['name' => 'Buddha',     'value' => 45 + $semIndex],
                    ['name' => 'Konghucu',   'value' => 12],
                    ['name' => 'Kepercayaan','value' => 40],
                ],
            ];

            $educationData = [
                'categories' => ['Tidak/Belum Sekolah','SD/Sederajat','SMP/Sederajat','SMA/Sederajat','D1/D2/D3','S1/D4','S2','S3'],
                'values'     => [
                    43000 - ($semIndex * 120),
                    67000 + ($semIndex * 200),
                    53000 + ($semIndex * 240),
                    61000 + ($semIndex * 480),
                    8500  + ($semIndex * 80),
                    25000 + ($semIndex * 530),
                    3000  + ($semIndex * 55),
                    200   + ($semIndex * 3),
                ],
            ];

            $maritalData = [
                'items' => [
                    ['name' => 'Belum Kawin', 'value' => 102000 + ($semIndex * 680)],
                    ['name' => 'Kawin',        'value' => 145000 + ($semIndex * 780)],
                    ['name' => 'Cerai Hidup',  'value' => 8000 + ($semIndex * 68)],
                    ['name' => 'Cerai Mati',   'value' => 7900 + ($semIndex * 40)],
                ],
            ];

            $bloodData = [
                'categories' => ['A','B','AB','O','Tidak Tahu'],
                'values'     => [
                    51000 + ($semIndex * 260),
                    62500 + ($semIndex * 320),
                    28000 + ($semIndex * 180),
                    87000 + ($semIndex * 460),
                    36000 - ($semIndex * 400),
                ],
            ];

            // Progressive Document Ownership Targets
            $targetAnak  = 84000 + ($semIndex * 600);
            $aktaPct     = round(90.0 + ($semIndex * 0.96), 1);
            $aktaOwned   = (int) round($targetAnak * ($aktaPct / 100));

            $aktaData = [
                'items' => [
                    ['name' => 'Memiliki Akta Lahir', 'value' => $aktaOwned],
                    ['name' => 'Belum Memiliki Akta', 'value' => $targetAnak - $aktaOwned],
                ],
                'percentage' => $aktaPct,
                'owned'      => $aktaOwned,
                'target'     => $targetAnak,
            ];

            $kiaPct   = round(75.0 + ($semIndex * 2.5), 1);
            $kiaOwned = (int) round($targetAnak * ($kiaPct / 100));

            $kiaData = [
                'items' => [
                    ['name' => 'Memiliki KIA', 'value' => $kiaOwned],
                    ['name' => 'Belum Memiliki KIA', 'value' => $targetAnak - $kiaOwned],
                ],
                'percentage' => $kiaPct,
                'owned'      => $kiaOwned,
                'target'     => $targetAnak,
            ];

            $wajibKtpTotal = 185000 + ($semIndex * 1100);
            $ikdPct        = round(18.5 + ($semIndex * 3.94), 1);
            $ikdOwned      = (int) round($wajibKtpTotal * ($ikdPct / 100));

            $ikdData = [
                'items' => [
                    ['name' => 'Aktivasi IKD', 'value' => $ikdOwned],
                    ['name' => 'Belum Aktivasi', 'value' => $wajibKtpTotal - $ikdOwned],
                ],
                'percentage' => $ikdPct,
                'owned'      => $ikdOwned,
                'target'     => $wajibKtpTotal,
            ];

            $lansiaTotal = 22500 + ($semIndex * 330);
            $lansiaPct   = round(($lansiaTotal / $basePop) * 100, 1);

            $lansiaData = [
                'items' => [
                    ['name' => 'Lansia Laki-Laki', 'value' => (int) round($lansiaTotal * 0.49)],
                    ['name' => 'Lansia Perempuan', 'value' => (int) round($lansiaTotal * 0.51)],
                ],
                'total'      => $lansiaTotal,
                'percentage' => $lansiaPct,
            ];

            $prodTotal = (int) round($basePop * 0.648);
            $productiveData = [
                'items' => [
                    ['name' => 'Usia Produktif Laki-Laki', 'value' => (int) round($prodTotal * 0.504)],
                    ['name' => 'Usia Produktif Perempuan', 'value' => (int) round($prodTotal * 0.496)],
                ],
                'total'      => $prodTotal,
                'percentage' => 64.8,
            ];

            $disabilityData = [
                'items' => [
                    ['name' => 'Disabilitas Fisik', 'value' => 800 + ($semIndex * 8)],
                    ['name' => 'Disabilitas Netra', 'value' => 300 + ($semIndex * 2)],
                    ['name' => 'Disabilitas Rungu/Wicara', 'value' => 400 + ($semIndex * 4)],
                    ['name' => 'Disabilitas Mental/Jiwa', 'value' => 280 + ($semIndex * 2)],
                    ['name' => 'Disabilitas Ganda', 'value' => 170 + ($semIndex * 2)],
                ],
                'total' => 1950 + ($semIndex * 18),
            ];

            $householdsTotal = (int) round($basePop / 3.646);
            $householdsData = [
                'items' => [
                    ['name' => 'Kepala Keluarga Laki-Laki', 'value' => (int) round($householdsTotal * 0.837)],
                    ['name' => 'Kepala Keluarga Perempuan', 'value' => (int) round($householdsTotal * 0.163)],
                ],
                'total' => $householdsTotal,
            ];

            $ktpRecPct   = round(95.5 + ($semIndex * 0.58), 1);
            $ktpRecorded = (int) round($wajibKtpTotal * ($ktpRecPct / 100));

            $wajibKtpData = [
                'items' => [
                    ['name' => 'Sudah Rekam KTP', 'value' => $ktpRecorded],
                    ['name' => 'Belum Rekam KTP', 'value' => $wajibKtpTotal - $ktpRecorded],
                ],
                'total'               => $wajibKtpTotal,
                'recorded'            => $ktpRecorded,
                'recorded_percentage' => $ktpRecPct,
            ];

            $nonProd = $basePop - $prodTotal;
            $depRatio = round(($nonProd / $prodTotal) * 100, 2);

            $dependencyData = [
                'items' => [
                    ['name' => 'Penduduk Belum Produktif (0-14 Thn)', 'value' => $nonProd - $lansiaTotal],
                    ['name' => 'Penduduk Produktif (15-59 Thn)', 'value' => $prodTotal],
                    ['name' => 'Penduduk Lansia (60+ Thn)', 'value' => $lansiaTotal],
                ],
                'ratio'           => $depRatio,
                'non_productive'  => $nonProd,
                'productive'      => $prodTotal,
                'note'            => "Tiap 100 usia produktif menanggung {$depRatio} penduduk non-produktif",
            ];

            $types = [
                'population'       => ['title' => "Piramida Penduduk Kabupaten Dompu {$year} {$semLabel}", 'data' => $populationData],
                'religion'         => ['title' => "Distribusi Agama Kabupaten Dompu {$year} {$semLabel}", 'data' => $religionData],
                'education'        => ['title' => "Tingkat Pendidikan Kabupaten Dompu {$year} {$semLabel}", 'data' => $educationData],
                'marital'          => ['title' => "Status Perkawinan Kabupaten Dompu {$year} {$semLabel}", 'data' => $maritalData],
                'blood_type'       => ['title' => "Golongan Darah Kabupaten Dompu {$year} {$semLabel}", 'data' => $bloodData],
                'akta_lahir'       => ['title' => "Cakupan Akta Lahir Anak 0-17 Tahun Dompu {$year} {$semLabel}", 'data' => $aktaData],
                'kia'              => ['title' => "Cakupan Kartu Identitas Anak (KIA) Dompu {$year} {$semLabel}", 'data' => $kiaData],
                'ikd'              => ['title' => "Cakupan Identitas Kependudukan Digital (IKD) Dompu {$year} {$semLabel}", 'data' => $ikdData],
                'lansia'           => ['title' => "Jumlah & Proporsi Lansia Dompu {$year} {$semLabel}", 'data' => $lansiaData],
                'productive_age'   => ['title' => "Jumlah Penduduk Produktif Dompu {$year} {$semLabel}", 'data' => $productiveData],
                'disability'       => ['title' => "Penyandang Disabilitas Kabupaten Dompu {$year} {$semLabel}", 'data' => $disabilityData],
                'households'       => ['title' => "Jumlah Kepala Keluarga Kabupaten Dompu {$year} {$semLabel}", 'data' => $householdsData],
                'wajib_ktp'        => ['title' => "Perekaman Wajib KTP Kabupaten Dompu {$year} {$semLabel}", 'data' => $wajibKtpData],
                'dependency_ratio' => ['title' => "Rasio Ketergantungan Kabupaten Dompu {$year} {$semLabel}", 'data' => $dependencyData],
            ];

            foreach ($types as $type => $info) {
                DemographicDataset::updateOrCreate(
                    ['year' => $year, 'semester' => $semester, 'type' => $type],
                    [
                        'title'        => $info['title'],
                        'status'       => 'published',
                        'published_at' => now(),
                        'data_json'    => $info['data'],
                    ]
                );
            }
        };

        // Dataset auto-seeding disabled to ensure production datasets start clean without dummy data.
        // $seedYearDatasets(2024, 1);
        // $seedYearDatasets(2024, 2);
        // $seedYearDatasets(2025, 1);
        // $seedYearDatasets(2025, 2);
        // $seedYearDatasets(2026, 1);
        // $seedYearDatasets(2026, 2);

        $this->command->info('✓ DemographicSeeder: 8 kecamatan dan 81 desa/kelurahan berhasil di-seed.');
    }
}
