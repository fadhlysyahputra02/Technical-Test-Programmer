<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding 10.000 projects...');

        // Fetch all applicant user IDs
        $applicantIds = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'applicant')
            ->where('model_has_roles.model_type', 'App\\Models\\User')
            ->pluck('users.id')
            ->toArray();

        if (empty($applicantIds)) {
            $this->command->warn('No applicant users found. Run UserSeeder first.');
            return;
        }

        $total     = 10000;
        $chunkSize = 500;
        $batches   = (int) ceil($total / $chunkSize);
        $now       = now()->toDateTimeString();
        $statuses  = ['active', 'inactive'];

        $prefixes = [
            'Pembangunan', 'Riset Pengembangan', 'Studi Kelayakan', 'Implementasi Sistem', 
            'Audit Infrastruktur', 'Pengadaan Alat', 'Evaluasi Dampak', 'Peningkatan Kapasitas',
            'Optimalisasi Layanan', 'Modernisasi Portal', 'Uji Coba Lapangan', 'Restrukturisasi'
        ];

        $objects = [
            'Teknologi Informasi', 'Kesehatan Masyarakat', 'Pendidikan Karakter', 'Sistem Logistik', 
            'Energi Terbarukan', 'Transportasi Massal', 'Ketahanan Pangan', 'Infrastruktur Jaringan',
            'Manajemen Kebencanaan', 'Pengolahan Limbah', 'Ekonomi Kreatif', 'Sistem Keamanan'
        ];

        $domains = [
            'Pemerintah Daerah', 'Sektor UMKM', 'Kawasan Pesisir', 'Pusat Kota', 
            'Rumah Sakit Umum', 'Sekolah Menengah', 'Kawasan Industri', 'Desa Wisata',
            'Kawasan Transmigrasi', 'Pusat Riset Nasional', 'Daerah Tertinggal', 'Kawasan Konservasi'
        ];

        $descriptions = [
            'Proyek ini bertujuan untuk menguji tingkat efisiensi dan keandalan sistem dalam jangka panjang.',
            'Studi komprehensif mengenai penerapan infrastruktur terintegrasi demi kenyamanan publik.',
            'Analisis mendalam terhadap dampak sosial, ekonomi, serta kelayakan teknis di lingkungan sasaran.',
            'Pengembangan berkelanjutan untuk meningkatkan produktivitas, transparansi, dan kualitas layanan terpadu.',
            'Audit berkala untuk memastikan semua protokol kepatuhan dan standar operasional berjalan optimal.',
            'Penyediaan fasilitas mutakhir guna mempercepat proses adopsi inovasi teknologi terapan.'
        ];

        for ($batch = 0; $batch < $batches; $batch++) {
            $count = ($batch === $batches - 1)
                ? $total - ($batch * $chunkSize)
                : $chunkSize;

            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $projectName = $prefixes[array_rand($prefixes)] . ' ' . 
                               $objects[array_rand($objects)] . ' di ' . 
                               $domains[array_rand($domains)];
                
                // Add unique sequence number to ensure variations for 10.000 records
                $projectName .= ' Batch-' . ($batch + 1) . '-' . ($i + 1);

                $rows[] = [
                    'name'         => $projectName,
                    'description'  => $descriptions[array_rand($descriptions)],
                    'applicant_id' => $applicantIds[array_rand($applicantIds)],
                    'status'       => $statuses[array_rand($statuses)],
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            DB::table('projects')->insert($rows);
        }

        $this->command->info('✓ 10.000 projects seeded.');
    }
}
