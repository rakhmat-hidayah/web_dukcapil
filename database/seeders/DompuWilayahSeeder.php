<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DompuWilayahSeeder extends Seeder
{
    /**
     * Seed authoritative Kecamatan & Desa/Kelurahan data for Kabupaten Dompu (52.05).
     * Total: 8 Kecamatan, 81 Desa/Kelurahan (9 Kelurahan, 72 Desa).
     */
    public function run(): void
    {
        $wilayah = [
            [
                'code'       => '52.05.01',
                'name'       => 'DOMPU',
                'ibukota'    => 'Dompu',
                'sort_order' => 1,
                'desas'      => [
                    ['code' => '52.05.01.1001', 'name' => 'BADA',         'type' => 'kelurahan', 'sort_order' => 1],
                    ['code' => '52.05.01.1002', 'name' => 'BALI',         'type' => 'kelurahan', 'sort_order' => 2],
                    ['code' => '52.05.01.1003', 'name' => 'POTU',         'type' => 'kelurahan', 'sort_order' => 3],
                    ['code' => '52.05.01.1004', 'name' => 'KARIJAWA',     'type' => 'kelurahan', 'sort_order' => 4],
                    ['code' => '52.05.01.1005', 'name' => 'DORA TANGGA',  'type' => 'kelurahan', 'sort_order' => 5],
                    ['code' => '52.05.01.1006', 'name' => 'KANDAI I',     'type' => 'kelurahan', 'sort_order' => 6],
                    ['code' => '52.05.01.2007', 'name' => 'O\'O',         'type' => 'desa',      'sort_order' => 7],
                    ['code' => '52.05.01.2008', 'name' => 'KATUA',        'type' => 'desa',      'sort_order' => 8],
                    ['code' => '52.05.01.2009', 'name' => 'KARAMABURA',   'type' => 'desa',      'sort_order' => 9],
                    ['code' => '52.05.01.2010', 'name' => 'MBAWI',        'type' => 'desa',      'sort_order' => 10],
                    ['code' => '52.05.01.2011', 'name' => 'DORE BARA',    'type' => 'desa',      'sort_order' => 11],
                    ['code' => '52.05.01.2012', 'name' => 'KAREKE',       'type' => 'desa',      'sort_order' => 12],
                    ['code' => '52.05.01.2013', 'name' => 'MANGGE NAE',   'type' => 'desa',      'sort_order' => 13],
                    ['code' => '52.05.01.2014', 'name' => 'MANGGEASI',    'type' => 'desa',      'sort_order' => 14],
                    ['code' => '52.05.01.2015', 'name' => 'SORI SAKOLO',  'type' => 'desa',      'sort_order' => 15],
                ],
            ],
            [
                'code'       => '52.05.02',
                'name'       => 'KEMPO',
                'ibukota'    => 'Kempo',
                'sort_order' => 2,
                'desas'      => [
                    ['code' => '52.05.02.2001', 'name' => 'KEMPO',       'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.02.2002', 'name' => 'TA\'A',        'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.02.2003', 'name' => 'SORO',        'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.02.2004', 'name' => 'KONTE',       'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.02.2005', 'name' => 'TOLO KALO',   'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.02.2006', 'name' => 'SONGGAJA',    'type' => 'desa', 'sort_order' => 6],
                    ['code' => '52.05.02.2007', 'name' => 'DORO KOBO',   'type' => 'desa', 'sort_order' => 7],
                    ['code' => '52.05.02.2008', 'name' => 'SORO BARAT',  'type' => 'desa', 'sort_order' => 8],
                ],
            ],
            [
                'code'       => '52.05.03',
                'name'       => 'HU\'U',
                'ibukota'    => 'Hu\'u',
                'sort_order' => 3,
                'desas'      => [
                    ['code' => '52.05.03.2001', 'name' => 'RASA BOU',    'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.03.2002', 'name' => 'DAHA',        'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.03.2003', 'name' => 'HU\'U',       'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.03.2004', 'name' => 'ADU',         'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.03.2005', 'name' => 'CEMPI JAYA',  'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.03.2006', 'name' => 'MERADA',      'type' => 'desa', 'sort_order' => 6],
                    ['code' => '52.05.03.2007', 'name' => 'JALA',        'type' => 'desa', 'sort_order' => 7],
                    ['code' => '52.05.03.2008', 'name' => 'SAWE',        'type' => 'desa', 'sort_order' => 8],
                ],
            ],
            [
                'code'       => '52.05.04',
                'name'       => 'KILO',
                'ibukota'    => 'Kilo',
                'sort_order' => 4,
                'desas'      => [
                    ['code' => '52.05.04.2001', 'name' => 'MALAJU',      'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.04.2002', 'name' => 'LASI',        'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.04.2003', 'name' => 'MBUJU',       'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.04.2004', 'name' => 'KIWU',        'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.04.2005', 'name' => 'TAROPO',      'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.04.2006', 'name' => 'KARAMA',      'type' => 'desa', 'sort_order' => 6],
                ],
            ],
            [
                'code'       => '52.05.05',
                'name'       => 'WOJA',
                'ibukota'    => 'Woja',
                'sort_order' => 5,
                'desas'      => [
                    ['code' => '52.05.05.1001', 'name' => 'MONTA BARU',  'type' => 'kelurahan', 'sort_order' => 1],
                    ['code' => '52.05.05.1002', 'name' => 'KANDAI II',   'type' => 'kelurahan', 'sort_order' => 2],
                    ['code' => '52.05.05.1003', 'name' => 'SIMPASAI',    'type' => 'kelurahan', 'sort_order' => 3],
                    ['code' => '52.05.05.2004', 'name' => 'SANEO',       'type' => 'desa',      'sort_order' => 4],
                    ['code' => '52.05.05.2005', 'name' => 'WAWONDURU',   'type' => 'desa',      'sort_order' => 5],
                    ['code' => '52.05.05.2006', 'name' => 'MATUA',       'type' => 'desa',      'sort_order' => 6],
                    ['code' => '52.05.05.2007', 'name' => 'NOWA',        'type' => 'desa',      'sort_order' => 7],
                    ['code' => '52.05.05.2008', 'name' => 'BARA',        'type' => 'desa',      'sort_order' => 8],
                    ['code' => '52.05.05.2009', 'name' => 'MADA PRAMA',  'type' => 'desa',      'sort_order' => 9],
                    ['code' => '52.05.05.2010', 'name' => 'RIWO',        'type' => 'desa',      'sort_order' => 10],
                    ['code' => '52.05.05.2011', 'name' => 'MUMBU',       'type' => 'desa',      'sort_order' => 11],
                    ['code' => '52.05.05.2012', 'name' => 'SERA KAPI',   'type' => 'desa',      'sort_order' => 12],
                    ['code' => '52.05.05.2013', 'name' => 'RABA BAKA',   'type' => 'desa',      'sort_order' => 13],
                    ['code' => '52.05.05.2014', 'name' => 'BAKA JAYA',   'type' => 'desa',      'sort_order' => 14],
                ],
            ],
            [
                'code'       => '52.05.06',
                'name'       => 'PEKAT',
                'ibukota'    => 'Pekat',
                'sort_order' => 6,
                'desas'      => [
                    ['code' => '52.05.06.2001', 'name' => 'PEKAT',         'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.06.2002', 'name' => 'NANGAMIRO',     'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.06.2003', 'name' => 'KADINDI',       'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.06.2004', 'name' => 'BERINGIN JAYA', 'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.06.2005', 'name' => 'SORI NOMO',     'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.06.2006', 'name' => 'TAMBORA',       'type' => 'desa', 'sort_order' => 6],
                    ['code' => '52.05.06.2007', 'name' => 'DORO PETI',     'type' => 'desa', 'sort_order' => 7],
                    ['code' => '52.05.06.2008', 'name' => 'KAROMBO',       'type' => 'desa', 'sort_order' => 8],
                    ['code' => '52.05.06.2009', 'name' => 'KADINDI BARAT', 'type' => 'desa', 'sort_order' => 9],
                    ['code' => '52.05.06.2010', 'name' => 'NANGAKARA',     'type' => 'desa', 'sort_order' => 10],
                    ['code' => '52.05.06.2011', 'name' => 'SORI TATANGA',  'type' => 'desa', 'sort_order' => 11],
                    ['code' => '52.05.06.2012', 'name' => 'CALABAI',       'type' => 'desa', 'sort_order' => 12],
                ],
            ],
            [
                'code'       => '52.05.07',
                'name'       => 'MANGGALEWA',
                'ibukota'    => 'Soriutu',
                'sort_order' => 7,
                'desas'      => [
                    ['code' => '52.05.07.2001', 'name' => 'SORIUTU',      'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.07.2002', 'name' => 'BANGGO',       'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.07.2003', 'name' => 'KWANGKO',      'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.07.2004', 'name' => 'LANCI JAYA',   'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.07.2005', 'name' => 'SUKA DAMAI',   'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.07.2006', 'name' => 'NUSA JAYA',    'type' => 'desa', 'sort_order' => 6],
                    ['code' => '52.05.07.2007', 'name' => 'DOROMELO',     'type' => 'desa', 'sort_order' => 7],
                    ['code' => '52.05.07.2008', 'name' => 'NANGATUMPU',   'type' => 'desa', 'sort_order' => 8],
                    ['code' => '52.05.07.2009', 'name' => 'KAMPASI MECI', 'type' => 'desa', 'sort_order' => 9],
                    ['code' => '52.05.07.2010', 'name' => 'TEKA SIRE',    'type' => 'desa', 'sort_order' => 10],
                    ['code' => '52.05.07.2011', 'name' => 'ANAMINA',      'type' => 'desa', 'sort_order' => 11],
                    ['code' => '52.05.07.2012', 'name' => 'TANJU',        'type' => 'desa', 'sort_order' => 12],
                ],
            ],
            [
                'code'       => '52.05.08',
                'name'       => 'PAJO',
                'ibukota'    => 'Pajo',
                'sort_order' => 8,
                'desas'      => [
                    ['code' => '52.05.08.2001', 'name' => 'RANGGO',       'type' => 'desa', 'sort_order' => 1],
                    ['code' => '52.05.08.2002', 'name' => 'JAMBU',        'type' => 'desa', 'sort_order' => 2],
                    ['code' => '52.05.08.2003', 'name' => 'LEPADI',       'type' => 'desa', 'sort_order' => 3],
                    ['code' => '52.05.08.2004', 'name' => 'LUNE',         'type' => 'desa', 'sort_order' => 4],
                    ['code' => '52.05.08.2005', 'name' => 'WOKO',         'type' => 'desa', 'sort_order' => 5],
                    ['code' => '52.05.08.2006', 'name' => 'TEMBAL LAE',   'type' => 'desa', 'sort_order' => 6],
                ],
            ],
        ];

        DB::transaction(function () use ($wilayah) {
            // Remove any legacy dummy kecamatan with code 5208 if no demographic datasets are bound
            $dummies = Kecamatan::where('code', 'like', '5208%')->get();
            foreach ($dummies as $dum) {
                if ($dum->datasets()->count() === 0) {
                    $dum->desas()->delete();
                    $dum->delete();
                }
            }

            foreach ($wilayah as $kW) {
                $cleanCode = str_replace('.', '', $kW['code']); // 520501

                // Find existing by dotted code, plain code, or name
                $kec = Kecamatan::where('code', $kW['code'])
                    ->orWhere('code', $cleanCode)
                    ->orWhereRaw('LOWER(name) = ?', [strtolower($kW['name'])])
                    ->first();

                if ($kec) {
                    $kec->update([
                        'name'       => strtoupper($kW['name']),
                        'code'       => $kW['code'],
                        'ibukota'    => $kW['ibukota'],
                        'desa_count' => count($kW['desas']),
                        'sort_order' => $kW['sort_order'],
                    ]);
                } else {
                    $kec = Kecamatan::create([
                        'name'       => strtoupper($kW['name']),
                        'code'       => $kW['code'],
                        'ibukota'    => $kW['ibukota'],
                        'desa_count' => count($kW['desas']),
                        'sort_order' => $kW['sort_order'],
                    ]);
                }

                foreach ($kW['desas'] as $dW) {
                    $cleanDesaCode = str_replace('.', '', $dW['code']); // 5205011001

                    $desa = Desa::where('code', $dW['code'])
                        ->orWhere('code', $cleanDesaCode)
                        ->orWhere(function ($q) use ($kec, $dW) {
                            $q->where('kecamatan_id', $kec->id)
                              ->whereRaw('LOWER(name) = ?', [strtolower($dW['name'])]);
                        })
                        ->first();

                    if ($desa) {
                        $desa->update([
                            'kecamatan_id' => $kec->id,
                            'name'         => strtoupper($dW['name']),
                            'code'         => $dW['code'],
                            'type'         => $dW['type'],
                            'sort_order'   => $dW['sort_order'],
                        ]);
                    } else {
                        Desa::create([
                            'kecamatan_id' => $kec->id,
                            'name'         => strtoupper($dW['name']),
                            'code'         => $dW['code'],
                            'type'         => $dW['type'],
                            'sort_order'   => $dW['sort_order'],
                        ]);
                    }
                }
            }
        });

        $this->command?->info('DompuWilayahSeeder completed: 8 Kecamatan and 81 Desa/Kelurahan synced successfully.');
    }
}
