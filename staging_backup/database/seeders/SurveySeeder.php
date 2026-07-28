<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SurveyPeriod;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\SurveyResponseAnswer;
use App\Models\SurveyRecommendation;
use App\Models\SurveyFollowUpAction;
use App\Services\SurveyCalculationService;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Active Survey Period
        $period = SurveyPeriod::updateOrCreate(
            ['year' => 2026, 'semester' => '1'],
            [
                'title' => 'Survei Kepuasan Masyarakat (IKM) Semester I Tahun 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'status' => 'published',
                'is_active' => true,
                'target_respondents' => 250,
            ]
        );

        // 2. Clear & Seed 9 Standard PermenPANRB No. 14/2017 Questions
        SurveyQuestion::where('survey_period_id', $period->id)->delete();

        $questionsData = [
            [
                'question_text' => 'Bagaimana pendapat Anda tentang kesesuaian persyaratan pelayanan dengan jenis pelayanannya?',
                'service_category' => 'Persyaratan Pelayanan',
                'sort_order' => 1,
            ],
            [
                'question_text' => 'Bagaimana kemudahan prosedur pelayanan administrasi kependudukan di loket maupun online?',
                'service_category' => 'Prosedur Pelayanan',
                'sort_order' => 2,
            ],
            [
                'question_text' => 'Bagaimana kecepatan waktu dalam memberikan pelayanan dan penyelesaian dokumen kependudukan?',
                'service_category' => 'Waktu Pelayanan',
                'sort_order' => 3,
            ],
            [
                'question_text' => 'Bagaimana kepastian dan kejelasan biaya pelayanan (Gratis Rp 0,- sesuai undang-undang)?',
                'service_category' => 'Biaya/Tarif',
                'sort_order' => 4,
            ],
            [
                'question_text' => 'Bagaimana kesesuaian antara produk hasil pelayanan yang diterima dengan ketentuan yang ditetapkan?',
                'service_category' => 'Produk Spesifikasi',
                'sort_order' => 5,
            ],
            [
                'question_text' => 'Bagaimana kompetensi, keahlian, dan ketrampilan petugas dalam memberikan pelayanan?',
                'service_category' => 'Kompetensi Pelaksana',
                'sort_order' => 6,
            ],
            [
                'question_text' => 'Bagaimana perilaku petugas meliputi kesopanan, keramahan, dan kesiapsiagaan dalam merespon pemohon?',
                'service_category' => 'Perilaku Pelaksana',
                'sort_order' => 7,
            ],
            [
                'question_text' => 'Bagaimana penanganan pengaduan, saran, dan masukan pengguna layanan kependudukan?',
                'service_category' => 'Penanganan Pengaduan',
                'sort_order' => 8,
            ],
            [
                'question_text' => 'Bagaimana kualitas dan kenyamanan sarana serta prasarana gedung/ruang tunggu pelayanan?',
                'service_category' => 'Sarana & Prasarana',
                'sort_order' => 9,
            ],
        ];

        $createdQuestions = [];
        foreach ($questionsData as $q) {
            $createdQuestions[] = SurveyQuestion::create([
                'survey_period_id' => $period->id,
                'question_text' => $q['question_text'],
                'question_type' => 'rating',
                'service_category' => $q['service_category'],
                'sort_order' => $q['sort_order'],
                'is_required' => true,
                'is_enabled' => true,
            ]);
        }

        // 3. Seed Sample Respondent Responses
        SurveyResponse::where('survey_period_id', $period->id)->delete();

        $sampleRespondents = [
            ['name' => 'Ahmad Dahlan', 'age' => '32', 'gender' => 'Laki-laki', 'education' => 'S1', 'job' => 'PNS', 'service' => 'Perekaman KTP-el'],
            ['name' => 'Siti Nurhaliza', 'age' => '28', 'gender' => 'Perempuan', 'education' => 'SMA', 'job' => 'Wiraswasta', 'service' => 'Kartu Keluarga (KK)'],
            ['name' => 'Budi Santoso', 'age' => '45', 'gender' => 'Laki-laki', 'education' => 'D3', 'job' => 'Karyawan Swasta', 'service' => 'Akta Kelahiran'],
            ['name' => 'Dewi Anggraini', 'age' => '24', 'gender' => 'Perempuan', 'education' => 'S1', 'job' => 'Mahasiswa', 'service' => 'Kartu Identitas Anak (KIA)'],
            ['name' => 'M. Rizky', 'age' => '39', 'gender' => 'Laki-laki', 'education' => 'SMA', 'job' => 'Petani', 'service' => 'Pindah Datang'],
            ['name' => 'Mariana', 'age' => '50', 'gender' => 'Perempuan', 'education' => 'SMP', 'job' => 'Ibu Rumah Tangga', 'service' => 'Akta Kematian'],
        ];

        $ratingScores = [4, 4, 3, 4, 4, 3, 4, 4, 3]; // High ratings averaging ~88.75 (A)

        foreach ($sampleRespondents as $r) {
            $response = SurveyResponse::create([
                'survey_period_id' => $period->id,
                'respondent_name' => $r['name'],
                'respondent_age' => $r['age'],
                'respondent_gender' => $r['gender'],
                'respondent_education' => $r['education'],
                'respondent_job' => $r['job'],
                'service_accessed' => $r['service'],
                'suggestion' => 'Pelayanan di loket sangat ramah dan cepat, mohon ruang tunggu ditambah pendingin ruangan.',
                'ikm_score' => 88.89,
            ]);

            foreach ($createdQuestions as $idx => $q) {
                $score = $ratingScores[$idx % count($ratingScores)];
                SurveyResponseAnswer::create([
                    'survey_response_id' => $response->id,
                    'survey_question_id' => $q->id,
                    'answer_value' => (string) $score,
                    'score' => $score,
                ]);
            }
        }

        // 4. Calculate Period Stats
        $calcService = new SurveyCalculationService();
        $calcService->calculatePeriodStats($period->id);

        // 5. Seed Recommendations
        SurveyRecommendation::where('survey_period_id', $period->id)->delete();

        $rec1 = SurveyRecommendation::create([
            'survey_period_id' => $period->id,
            'title' => 'Peningkatan Fasilitas Pendingin & Ruang Tunggu Loket',
            'description' => 'Menambahkan AC dan penataan ulang tempat duduk untuk meningkatkan kenyamanan sarana prasarana.',
            'priority' => 'high',
            'status' => 'in_progress',
            'target_completion' => '2026-05-15',
            'pic' => 'Kasubag Umum & Keuangan',
        ]);

        $rec2 = SurveyRecommendation::create([
            'survey_period_id' => $period->id,
            'title' => 'Bimbingan Teknis Standar Pelayanan Kesiapsiagaan Petugas Front Office',
            'description' => 'Pelatihan peningkatan hospitality dan keramahan petugas loket pelayanan publik.',
            'priority' => 'medium',
            'status' => 'completed',
            'target_completion' => '2026-03-30',
            'pic' => 'Kabid Pelayanan Pendaftaran Penduduk',
        ]);

        // 6. Seed Follow-up Actions
        SurveyFollowUpAction::where('survey_period_id', $period->id)->delete();

        SurveyFollowUpAction::create([
            'survey_period_id' => $period->id,
            'recommendation_id' => $rec1->id,
            'action_name' => 'Pengadaan & Pemasangan 4 Unit AC Ceiling Floor Loket Utama',
            'description' => 'Pemasangan AC baru di area antrean warga untuk menjaga suhu nyaman.',
            'responsible_unit' => 'Subbag Umum & Perlengkapan',
            'progress' => 85,
            'completion_date' => '2026-05-10',
            'status' => 'on_track',
        ]);

        SurveyFollowUpAction::create([
            'survey_period_id' => $period->id,
            'recommendation_id' => $rec2->id,
            'action_name' => 'Pelaksanaan Workshop Excellent Service Petugas Frontliner',
            'description' => 'Pelatihan kerja sama dengan praktisi pelayanan publik terakreditasi.',
            'responsible_unit' => 'Bidang Pemanfaatan Data & Inovasi',
            'progress' => 100,
            'completion_date' => '2026-03-25',
            'status' => 'completed',
        ]);
    }
}
