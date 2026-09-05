<?php

namespace App\Http\Controllers;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Concerns\BackupModuleTrait;use App\Http\Controllers\Concerns\SecurityModuleTrait;use App\Http\Controllers\Concerns\SettingsModuleTrait;use App\Http\Controllers\Concerns\KontenModuleTrait;use App\Http\Controllers\Concerns\KkModuleTrait;use App\Http\Controllers\Concerns\PastorModuleTrait;use App\Http\Controllers\Concerns\GaleriModuleTrait;use App\Http\Controllers\Concerns\ProfileModuleTrait;use App\Http\Controllers\Concerns\StatistikModuleTrait;use App\Http\Controllers\Concerns\UserModuleTrait;use App\Http\Controllers\Concerns\PembersihModuleTrait;use App\Http\Controllers\Concerns\ParokiModuleTrait;use App\Http\Controllers\Concerns\UmatModuleTrait;use App\Http\Controllers\Concerns\GenericModuleTrait;

class InertiaPanelController extends Controller
{
    use BackupModuleTrait;    use SecurityModuleTrait;    use SettingsModuleTrait;    use KontenModuleTrait;    use KkModuleTrait;    use PastorModuleTrait;    use GaleriModuleTrait;    use ProfileModuleTrait;    use StatistikModuleTrait;    use UserModuleTrait;    use PembersihModuleTrait;    use ParokiModuleTrait;    use UmatModuleTrait;    use GenericModuleTrait;
    /**
     * Dashboard SPA with zero-loader instant transitions & rich stats.
     */

    /**
     * Umat Index SPA with instant reactive search & pagination.
     */

    /**
     * Dedicated Full Page for Creating KK Katolik.
     */

    /**
     * Dedicated Full Page for Editing KK Katolik.
     */

    /**
     * Dedicated Full Page for Creating Umat / Jiwa Baru.
     */

    /**
     * Dedicated Full Page for Editing Umat / Jiwa.
     */

    /**
     * Store newly created Umat record.
     */

    /**
     * Update existing Umat record.
     */

    /**
     * Dedicated Full Page for Creating Master Pastor / Imam Baru.
     */

    /**
     * Dedicated Full Page for Editing Master Pastor / Imam.
     */

    /**
     * Store newly created Pastor record.
     */

    /**
     * Update existing Pastor record.
     */




    /**
     * Dedicated View Page for KK Katolik Details.
     */

    /**
     * Official PDF / Print View for KK Katolik.
     */

    /**
     * Dedicated Full Page for Creating Galeri / Album Baru.
     */

    /**
     * Dedicated Full Page for Editing Galeri / Album.
     */

    /**
     * Profil Paroki SPA.
     */

    /**
     * Profil Saya (User Profile & Account Settings SPA matching http://localhost/katedral/admin/profil-saya).
     */

    /**
     * Update Profil Saya (Personal Details & Avatar).
     */

    /**
     * Update Password Profil Saya.
     */

    /**
     * Dedicated Page for Backup & Restore Database.
     */

    /**
     * Generate Live MySQL SQL Database Backup.
     */

    /**
     * Download Backup File.
     */

    /**
     * Restore Database from existing backup record.
     */

    /**
     * Upload and Restore Database from SQL file.
     */

    /**
     * Delete Backup record and physical file.
     */



    /**
     * Security Center & Firewall Dashboard.
     */

    /**
     * Update security policies and settings.
     */

    /**
     * Block IP manually.
     */

    /**
     * Unblock IP.
     */

    /**
     * Clear or trim security audit logs.
     */

    /**
     * Clear system security cache.
     */

    /**
     * Ensure security tables exist in database.
     */

    /**
     * Settings Hub (Pengaturan Terpadu: Pembayaran, OTP, Video, Slider, SEO, Widget).
     */

    /**
     * Save / Update Payment Method.
     */

    /**
     * Delete Payment Method.
     */

    /**
     * Save OTP & WhatsApp Gateway Settings.
     */

    /**
     * Test Send WhatsApp Notification.
     */

    /**
     * Save Video Header Settings.
     */

    /**
     * Save Slider Banner.
     */

    /**
     * Delete Slider Banner.
     */

    /**
     * Save SEO & Meta Tags.
     */

    /**
     * Save Widget & Social Media Settings.
     */

    /**
     * Save Maintenance Mode Settings.
     */

    /**
     * Ensure Settings Hub tables exist in database.
     */

    /**
     * Demografi & Statistik Paroki SPA (Matches http://localhost/katedral/admin/demografi).
     */

    /**
     * Panduan Peran & Hak Akses (RBAC Matrix).
     */

    /**
     * Form Tambah Role & Hak Akses (Halaman Baru / Full Page)
     */

    /**
     * Form Edit Role & Hak Akses (Halaman Baru / Full Page)
     */






    /**
     * Dynamic handler for all pastoral & church modules.
     */

    /**
     * Store new record for pastoral modules with file upload support.
     */

    /**
     * Update existing record in database.
     */

    /**
     * Delete record from database.
     */

    /**
     * Export generic module data to Excel (CSV), PDF, or Printable View.
     */

    /**
     * Import generic module data from CSV / Excel file.
     */
    /**
     * Import generic module data from CSV / Excel file with strict anti-duplicate validation.
     */






    private function generateKontenExcerpt(string $content, int $limit = 180): string
    {
        return $this->cleanKontenExcerpt($content, '', $limit);
    }

    private function cleanKontenExcerpt(string $excerpt, string $fallbackContent = '', int $limit = 180): string
    {
        $text = html_entity_decode(html_entity_decode(strip_tags($excerpt), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\[[^\]]+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', (string) $text);
        $text = trim((string) $text);

        if ($text === '' && $fallbackContent !== '') {
            return $this->generateKontenExcerpt($fallbackContent, $limit);
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $trimmed = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($trimmed, ' ');

        if ($lastSpace !== false && $lastSpace > 50) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }

        return rtrim($trimmed) . '...';
    }

    /**
     * Lightweight audit trail. Persists an entry into security_logs for
     * sensitive module mutations (keuangan, user, role, etc.) so that
     * financial and administrative changes are traceable.
     */
    private function logAudit(string $event, string $slug, $id, array $payload = []): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('security_logs')) {
                return;
            }
            $safe = [];
            foreach ($payload as $k => $v) {
                if (in_array($k, ['password', 'remember_token'], true)) {
                    continue;
                }
                $safe[$k] = is_scalar($v) ? $v : null;
            }
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'ip_address' => request()->ip(),
                'user_id' => auth()->id(),
                'username' => auth()->user()?->username ?? auth()->user()?->email ?? 'system',
                'event_type' => $event,
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'status' => 'SUCCESS',
                'details' => 'Modul: ' . $slug . ' #' . ($id ?? '-') . ' | ' . json_encode($safe, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }







    private function styledExcelDownload(string $title, array $headings, $rows, string $fileName, bool $isTemplate = false)
    {
        $rowData = $rows instanceof \Illuminate\Support\Collection ? $rows->values()->all() : array_values($rows);

        if ($isTemplate) {
            $ecclRefs = $this->buildEcclesiasticalReferenceData();
            $civilRefs = $this->buildCivilReferenceData();

            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($title, $headings, $rowData, $ecclRefs, $civilRefs) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                    public function __construct(
                        private string $title,
                        private array $headings,
                        private array $rows,
                        private array $ecclRefs,
                        private array $civilRefs
                    ) {
                    }

                    public function sheets(): array
                    {
                        return [
                            // Sheet 1: Input Data Utama
                            new class($this->title, $this->headings, $this->rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(
                                    private string $title,
                                    private array $headings,
                                    private array $rows
                                ) {
                                }

                                public function array(): array
                                {
                                    $body = [
                                        ['FORMULIR PENGISIAN: ' . strtoupper($this->title)],
                                        ['Petunjuk: Isi data mulai baris ke-5 (baris kuning adalah contoh). Lihat Sheet REFERENSI_GEREJAWI & REFERENSI_SIPIL untuk ejaan baku.'],
                                        [],
                                        array_merge(['No'], $this->headings),
                                    ];

                                    foreach ($this->rows as $index => $row) {
                                        $body[] = array_merge([$index + 1], array_values($row));
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'DATA_INPUT';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings) + 1);
                                            $lastRow = max(5, count($this->rows) + 4);

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);
                                            $sheet->getColumnDimension('A')->setWidth(7);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'CBD5E1'],
                                                    ],
                                                ],
                                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                                            ]);

                                            // Baris Contoh Isian Berwarna Kuning Pastel
                                            $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']],
                                                'font' => ['italic' => true],
                                            ]);

                                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                                'alignment' => ['horizontal' => 'center'],
                                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                                            ]);
                                        },
                                    ];
                                }
                            },

                            // Sheet 2: Referensi Wilayah Gerejawi
                            new class($this->ecclRefs) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(private array $ecclRefs)
                                {
                                }

                                public function array(): array
                                {
                                    $headings = array_keys($this->ecclRefs);
                                    $maxCount = 0;
                                    foreach ($this->ecclRefs as $list) {
                                        $maxCount = max($maxCount, count($list));
                                    }

                                    $body = [
                                        ['DAFTAR REFERENSI RESMI WILAYAH GEREJAWI & STATUS'],
                                        ['Salin teks resmi di bawah ini ke Sheet DATA_INPUT agar relasi database otomatis terhubung dengan valid.'],
                                        [],
                                        array_merge(['No'], $headings),
                                    ];

                                    for ($i = 0; $i < $maxCount; $i++) {
                                        $row = [$i + 1];
                                        foreach ($headings as $heading) {
                                            $row[] = $this->ecclRefs[$heading][$i] ?? '';
                                        }
                                        $body[] = $row;
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'REFERENSI_GEREJAWI';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $headings = array_keys($this->ecclRefs);
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headings) + 1);
                                            $lastRow = max(5, (int) $sheet->getHighestRow());

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '172554']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'BFDBFE'],
                                                    ],
                                                ],
                                            ]);
                                        },
                                    ];
                                }
                            },

                            // Sheet 3: Referensi Wilayah Sipil & Administrasi
                            new class($this->civilRefs) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(private array $civilRefs)
                                {
                                }

                                public function array(): array
                                {
                                    $headings = array_keys($this->civilRefs);
                                    $maxCount = 0;
                                    foreach ($this->civilRefs as $list) {
                                        $maxCount = max($maxCount, count($list));
                                    }

                                    $body = [
                                        ['DAFTAR REFERENSI WILAYAH SIPIL & DEMOGRAFI'],
                                        ['Gunakan ejaan wilayah dan opsi standar kependudukan di bawah ini untuk mengisi data sipil.'],
                                        [],
                                        array_merge(['No'], $headings),
                                    ];

                                    for ($i = 0; $i < $maxCount; $i++) {
                                        $row = [$i + 1];
                                        foreach ($headings as $heading) {
                                            $row[] = $this->civilRefs[$heading][$i] ?? '';
                                        }
                                        $body[] = $row;
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'REFERENSI_SIPIL';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $headings = array_keys($this->civilRefs);
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headings) + 1);
                                            $lastRow = max(5, (int) $sheet->getHighestRow());

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '064E3B']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '059669']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '022C22']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'A7F3D0'],
                                                    ],
                                                ],
                                            ]);
                                        },
                                    ];
                                }
                            },
                        ];
                    }
                },
                $fileName
            );
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($title, $headings, $rowData, $isTemplate) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                public function __construct(
                    private string $title,
                    private array $headings,
                    private array $rows,
                    private bool $isTemplate
                ) {
                }

                public function array(): array
                {
                    $body = [
                        [strtoupper($this->title)],
                        ['SIPAROKI - Sistem Informasi Paroki | Dicetak: ' . now()->format('d-m-Y H:i') . ' WITA'],
                        [],
                        array_merge(['No'], $this->headings),
                    ];

                    foreach ($this->rows as $index => $row) {
                        $body[] = array_merge([$index + 1], array_values($row));
                    }

                    if (empty($this->rows)) {
                        $body[] = array_merge([''], array_fill(0, count($this->headings), ''));
                    }

                    return $body;
                }

                public function title(): string
                {
                    return \Illuminate\Support\Str::limit(preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]+/', '', $this->title), 31, '');
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings) + 1);
                            $lastRow = max(5, count($this->rows) + 4);

                            $sheet->mergeCells("A1:{$lastColumn}1");
                            $sheet->mergeCells("A2:{$lastColumn}2");
                            $sheet->freezePane('A5');
                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                            $sheet->getRowDimension(1)->setRowHeight(28);
                            $sheet->getRowDimension(2)->setRowHeight(22);
                            $sheet->getRowDimension(4)->setRowHeight(24);
                            $sheet->getColumnDimension('A')->setWidth(7);

                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F5597']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '203864']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                            ]);

                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['rgb' => 'B0C4DE'],
                                    ],
                                ],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);

                            for ($row = 5; $row <= $lastRow; $row++) {
                                $fill = $row % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
                                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $fill]],
                                ]);
                            }

                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                'alignment' => ['horizontal' => 'center'],
                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                            ]);
                        },
                    ];
                }
            },
            $fileName
        );
    }















    private function defaultParokiIdFromProfile(): ?int
    {
        $sessionParokiId = session()->get('default_paroki_id');
        if ($sessionParokiId && Paroki::whereKey($sessionParokiId)->exists()) {
            return (int) $sessionParokiId;
        }

        $profileParokiId = Schema::hasTable('profil_paroki')
            ? DB::table('profil_paroki')->whereNotNull('paroki_id')->value('paroki_id')
            : null;

        if ($profileParokiId && Paroki::whereKey($profileParokiId)->exists()) {
            return (int) $profileParokiId;
        }

        $settingParokiId = Schema::hasTable('pengaturan_aplikasi')
            ? DB::table('pengaturan_aplikasi')->whereNotNull('paroki_id')->value('paroki_id')
            : null;

        if ($settingParokiId && Paroki::whereKey($settingParokiId)->exists()) {
            return (int) $settingParokiId;
        }

        return Paroki::where('nama_paroki', 'like', '%Benlutu%')->value('id_paroki')
            ?? Paroki::value('id_paroki');
    }


    private function formatExportStatus($value): string
    {
        if ($value === 'Y' || $value === 1 || $value === true) {
            return 'Aktif';
        }
        if ($value === 'N' || $value === 0 || $value === false) {
            return 'Tidak Aktif';
        }
        return $value ?: 'Aktif';
    }








    private function clearFastAccessCache(): void
    {
        foreach ([
            'frontend.common_data',
            'frontend.beranda.jadwal_misa',
            'frontend.beranda.pengumuman',
            'frontend.beranda.galeri',
            'frontend.beranda.artikel',
            'frontend.beranda.stats',
            'frontend.kapela_geojson',
            'global_pengaturan_aplikasi_first',
        ] as $key) {
            Cache::forget($key);
        }

        Cache::forever('global_view_data_version', (int) Cache::get('global_view_data_version', 1) + 1);
    }






    /**
     * Safely synchronize paroki data into global settings (profil_paroki and pengaturan_aplikasi)
     * using dynamic table schema introspection.
     */






    private function getModuleMap(): array
    {
        return [
            'keuskupan' => [
                'model' => \App\Models\Keuskupan::class,
                'title' => 'Data Keuskupan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'logo', 'label' => 'Logo', 'isImage' => true],
                    ['key' => 'nama_keuskupan', 'label' => 'Nama Keuskupan', 'isPrimary' => true],
                    ['key' => 'nama_latin', 'altKey' => 'nama_keuskupan_latin', 'label' => 'Nama Latin'],
                    ['key' => 'kode_keuskupan', 'label' => 'Kode'],
                    ['key' => 'uskup', 'altKey' => 'nama_uskup', 'label' => 'Nama Uskup'],
                    ['key' => 'dekenats', 'label' => 'Dekenat', 'isRelationLink' => true, 'relation' => 'dekenats', 'linkTo' => 'dekenat', 'filterParam' => 'keuskupan_id', 'icon' => 'fa-layer-group', 'color' => 'blue'],
                    ['key' => 'no_telp', 'altKey' => 'telepon', 'label' => 'Kontak'],
                ],
            ],
            'dekenat' => [
                'model' => \App\Models\Dekenat::class,
                'title' => 'Data Kevikepan / Dekenat',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                    ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                    ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                    ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                    ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                    ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
                ],
            ],
            'kevikepan' => [
                'model' => \App\Models\Kevikepan::class,
                'title' => 'Data Kevikepan / Dekenat',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                    ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                    ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                    ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                    ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                    ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
                ],
            ],
            'paroki' => [
                'model' => \App\Models\Paroki::class,
                'title' => 'Data Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'logo', 'label' => 'Logo', 'isImage' => true],
                    ['key' => 'nama_paroki', 'label' => 'Nama Paroki', 'isPrimary' => true],
                    ['key' => 'kode_paroki', 'label' => 'Kode'],
                    ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                    ['key' => 'dekenat_nama', 'relation' => 'dekenat', 'relationKey' => 'nama_kevikepan', 'altRelationKey' => 'nama_dekenat', 'label' => 'Kevikepan / Dekenat'],
                    ['key' => 'pelindung_paroki', 'altKey' => 'pelindung', 'label' => 'Pelindung'],
                    ['key' => 'nama_pastor_paroki_aktif', 'altKey' => 'pastor_paroki', 'label' => 'Pastor Paroki'],
                    ['key' => 'alamat', 'label' => 'Alamat'],
                    ['key' => 'telepon', 'label' => 'Kontak'],
                ],
            ],
            'kuasi-paroki' => [
                'model' => \App\Models\KuasiParoki::class,
                'title' => 'Data Kuasi Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kuasi', 'altKey' => 'NamaKuasiParoki', 'label' => 'Nama Kuasi Paroki', 'isPrimary' => true],
                    ['key' => 'KodeKuasiParoki', 'altKey' => 'kode_kuasi', 'label' => 'Kode'],
                    ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'],
                    ['key' => 'dekenat_nama', 'label' => 'Kevikepan'],
                    ['key' => 'pastor_administrator', 'altKey' => 'PastorKuasiParoki', 'label' => 'Pastor Administrator'],
                    ['key' => 'lokasi', 'altKey' => 'AlamatKuasiParoki', 'label' => 'Lokasi / Alamat'],
                ],
            ],
            'kapela' => [
                'model' => \App\Models\Kapela::class,
                'title' => 'Data Stasi / Kapela',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kapela', 'label' => 'Nama Stasi / Kapela', 'isPrimary' => true],
                    ['key' => 'kode_kapela', 'label' => 'Kode'],
                    ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'],
                    ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'kapela_id', 'icon' => 'fa-people-group', 'color' => 'teal'],
                    ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'],
                    ['key' => 'lokasi', 'label' => 'Lokasi'],
                    ['key' => 'no_hp', 'label' => 'Kontak'],
                ],
            ],
            'wilayah' => [
                'model' => \App\Models\Wilayah::class,
                'title' => 'Data Wilayah Pelayanan',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_wilayah', 'label' => 'Nama Wilayah', 'isPrimary' => true],
                    ['key' => 'kode_wilayah', 'label' => 'Kode'],
                    ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'],
                    ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'],
                    ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'wilayah_id', 'icon' => 'fa-people-group', 'color' => 'teal'],
                    ['key' => 'ketua_wilayah', 'label' => 'Ketua Wilayah'],
                    ['key' => 'no_hp', 'label' => 'Kontak'],
                ],
            ],
            'lingkungan' => [
                'model' => \App\Models\Lingkungan::class,
                'title' => 'Lingkungan',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_lingkungan', 'label' => 'Nama Lingkungan', 'isPrimary' => true],
                    ['key' => 'kode_lingkungan', 'label' => 'Kode'],
                    ['key' => 'ketua_lingkungan', 'label' => 'Ketua Lingkungan'],
                ],
            ],
            'kub' => [
                'model' => \App\Models\Kub::class,
                'title' => 'Komunitas Umat Basis (KUB)',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kub', 'label' => 'Nama KUB', 'isPrimary' => true],
                    ['key' => 'kode_kub', 'label' => 'Kode'],
                    ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah'],
                    ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'],
                    ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'],
                    ['key' => 'ketua_kub', 'label' => 'Ketua KUB'],
                    ['key' => 'kontak', 'altKey' => 'no_hp', 'label' => 'Kontak'],
                ],
            ],
            'kk-katolik' => [
                'model' => \App\Models\KkKatolik::class,
                'title' => 'Kartu Keluarga (KK) Katolik',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto Kepala', 'isImage' => true],
                    ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                    ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                    ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                    ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                    ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                    ['key' => 'status_kk', 'label' => 'Status'],
                ],
            ],
            'kk' => [
                'model' => \App\Models\KkKatolik::class,
                'title' => 'Kartu Keluarga (KK) Katolik',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto Kepala', 'isImage' => true],
                    ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                    ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                    ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                    ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                    ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                    ['key' => 'status_kk', 'label' => 'Status'],
                ],
            ],
            'keluarga' => [
                'model' => \App\Models\KkKatolik::class,
                'title' => 'Kartu Keluarga (KK) Katolik',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto Kepala', 'isImage' => true],
                    ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                    ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                    ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                    ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                    ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                    ['key' => 'status_kk', 'label' => 'Status'],
                ],
            ],
            'umat' => [
                'model' => \App\Models\Umat::class,
                'title' => 'Data Umat / Jiwa Paroki',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                    ['key' => 'nik', 'label' => 'NIK'],
                    ['key' => 'no_kk_kw', 'label' => 'No KK'],
                    ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                    ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                    ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                    ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                    ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                    ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
                ],
            ],
            'data-umat' => [
                'model' => \App\Models\Umat::class,
                'title' => 'Data Umat / Jiwa Paroki',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                    ['key' => 'nik', 'label' => 'NIK'],
                    ['key' => 'no_kk_kw', 'label' => 'No KK'],
                    ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                    ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                    ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                    ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                    ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                    ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
                ],
            ],
            'jiwa' => [
                'model' => \App\Models\Umat::class,
                'title' => 'Data Umat / Jiwa Paroki',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                    ['key' => 'nik', 'label' => 'NIK'],
                    ['key' => 'no_kk_kw', 'label' => 'No KK'],
                    ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                    ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                    ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                    ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                    ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                    ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
                ],
            ],
            'riwayat-mutasi-umat' => [
                'model' => \App\Models\RiwayatMutasiUmat::class,
                'title' => 'Riwayat Mutasi Umat & KUB',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'umat_nama', 'relation' => 'umat', 'relationKey' => 'nama_lengkap', 'altRelationKey' => 'nama_baptis', 'label' => 'Nama Umat', 'isPrimary' => true],
                    ['key' => 'jenis_mutasi', 'label' => 'Jenis Mutasi'],
                    ['key' => 'kub_asal', 'relation' => 'kubAsal', 'relationKey' => 'nama_kub', 'label' => 'KUB Asal'],
                    ['key' => 'kub_tujuan', 'relation' => 'kubTujuan', 'relationKey' => 'nama_kub', 'label' => 'KUB Tujuan'],
                    ['key' => 'wilayah_asal', 'relation' => 'wilayahAsal', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Asal'],
                    ['key' => 'wilayah_tujuan', 'relation' => 'wilayahTujuan', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Tujuan'],
                    ['key' => 'tgl_mutasi', 'label' => 'Tanggal Mutasi', 'isDate' => true],
                    ['key' => 'alasan', 'label' => 'Alasan / Keterangan'],
                ],
            ],
            'mutasi-umat' => [
                'model' => \App\Models\RiwayatMutasiUmat::class,
                'title' => 'Riwayat Mutasi Umat & KUB',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'umat_nama', 'relation' => 'umat', 'relationKey' => 'nama_lengkap', 'altRelationKey' => 'nama_baptis', 'label' => 'Nama Umat', 'isPrimary' => true],
                    ['key' => 'jenis_mutasi', 'label' => 'Jenis Mutasi'],
                    ['key' => 'kub_asal', 'relation' => 'kubAsal', 'relationKey' => 'nama_kub', 'label' => 'KUB Asal'],
                    ['key' => 'kub_tujuan', 'relation' => 'kubTujuan', 'relationKey' => 'nama_kub', 'label' => 'KUB Tujuan'],
                    ['key' => 'wilayah_asal', 'relation' => 'wilayahAsal', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Asal'],
                    ['key' => 'wilayah_tujuan', 'relation' => 'wilayahTujuan', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Tujuan'],
                    ['key' => 'tgl_mutasi', 'label' => 'Tanggal Mutasi', 'isDate' => true],
                    ['key' => 'alasan', 'label' => 'Alasan / Keterangan'],
                ],
            ],
            'riwayat-mutasi' => [
                'model' => \App\Models\RiwayatMutasiUmat::class,
                'title' => 'Riwayat Mutasi Umat & KUB',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'umat_nama', 'relation' => 'umat', 'relationKey' => 'nama_lengkap', 'altRelationKey' => 'nama_baptis', 'label' => 'Nama Umat', 'isPrimary' => true],
                    ['key' => 'jenis_mutasi', 'label' => 'Jenis Mutasi'],
                    ['key' => 'kub_asal', 'relation' => 'kubAsal', 'relationKey' => 'nama_kub', 'label' => 'KUB Asal'],
                    ['key' => 'kub_tujuan', 'relation' => 'kubTujuan', 'relationKey' => 'nama_kub', 'label' => 'KUB Tujuan'],
                    ['key' => 'wilayah_asal', 'relation' => 'wilayahAsal', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Asal'],
                    ['key' => 'wilayah_tujuan', 'relation' => 'wilayahTujuan', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Tujuan'],
                    ['key' => 'tgl_mutasi', 'label' => 'Tanggal Mutasi', 'isDate' => true],
                    ['key' => 'alasan', 'label' => 'Alasan / Keterangan'],
                ],
            ],
            'sakramen' => [
                'model' => \App\Models\Sakramen::class,
                'title' => 'Buku Sakramen',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'umat', 'relation' => 'umat', 'relationKey' => 'nama_lengkap', 'label' => 'Nama Penerima', 'isPrimary' => true],
                    ['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen'],
                    ['key' => 'tanggal', 'label' => 'Tanggal Penerimaan', 'isDate' => true],
                    ['key' => 'tempat', 'label' => 'Tempat / Paroki'],
                    ['key' => 'pastor', 'altKey' => 'pelaksana', 'label' => 'Pastor / Pelayan'],
                    ['key' => 'wali_baptis', 'altKey' => 'nama_pasangan', 'label' => 'Wali / Pasangan'],
                    ['key' => 'liber_no', 'label' => 'No. Liber (Buku)'],
                ],
            ],
            'pengajuan-sakramen' => [
                'model' => \App\Models\PengajuanSakramen::class,
                'title' => 'Pengajuan & Administrasi Sakramen',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_lengkap', 'label' => 'Nama Pemohon / Penerima', 'isPrimary' => true],
                    ['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen'],
                    ['key' => 'whatsapp', 'label' => 'No. WhatsApp'],
                    ['key' => 'tanggal_pelaksanaan', 'label' => 'Tgl Pelaksanaan', 'isDate' => true],
                    ['key' => 'biaya_administrasi', 'label' => 'Biaya Admin (Rp)'],
                    ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
                    ['key' => 'status_pengajuan', 'label' => 'Status Pengajuan'],
                ],
            ],
            'jadwal-misa' => [
                'model' => \App\Models\JadwalMisa::class,
                'title' => 'Jadwal Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'jenis_perayaan', 'altKey' => 'jenis_misa', 'label' => 'Nama Misa', 'isPrimary' => true],
                    ['key' => 'tanggal', 'label' => 'Tanggal'],
                    ['key' => 'hari', 'label' => 'Hari'],
                    ['key' => 'waktu', 'altKey' => 'jam_perayaan', 'label' => 'Waktu'],
                    ['key' => 'tempat', 'altKey' => 'lokasi', 'label' => 'Gereja / Tempat'],
                ],
            ],
            'jenis-iuran' => [
                'model' => \App\Models\JenisIuran::class,
                'title' => 'Daftar Jenis Iuran Umat',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'kode_iuran', 'label' => 'Kode'],
                    ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                    ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                    ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                    ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                    ['key' => 'periode', 'label' => 'Periode'],
                    ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'jenis_iuran' => [
                'model' => \App\Models\JenisIuran::class,
                'title' => 'Daftar Jenis Iuran Umat',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'kode_iuran', 'label' => 'Kode'],
                    ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                    ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                    ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                    ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                    ['key' => 'periode', 'label' => 'Periode'],
                    ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'iuran' => [
                'model' => \App\Models\Iuran::class,
                'title' => 'Pencatatan Iuran Umat',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'no_kk', 'label' => 'No KK'],
                    ['key' => 'nama_kepala', 'label' => 'Kepala Keluarga', 'isPrimary' => true],
                    ['key' => 'nama_iuran', 'label' => 'Jenis Iuran'],
                    ['key' => 'tahun', 'label' => 'Tahun'],
                    ['key' => 'bulan_lunas', 'label' => 'Bulan Lunas'],
                    ['key' => 'total_jumlah', 'altKey' => 'jumlah', 'label' => 'Total Bayar (Rp)'],
                    ['key' => 'status_bayar', 'label' => 'Status'],
                    ['key' => 'tanggal_bayar', 'label' => 'Tgl Bayar', 'isDate' => true],
                    ['key' => 'kolektor', 'altKey' => 'petugas', 'label' => 'Petugas / Kolektor'],
                ],
            ],
            'kolekte' => [
                'model' => \App\Models\Kolekte::class,
                'title' => 'Pencatatan Kolekte Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'tanggal', 'label' => 'Tanggal Misa', 'isDate' => true, 'isPrimary' => true],
                    ['key' => 'kategori_misa', 'label' => 'Kategori / Perayaan Misa'],
                    ['key' => 'nominal', 'label' => 'Jumlah Kolekte (Rp)'],
                    ['key' => 'lokasi_misa', 'label' => 'Gereja / Tempat'],
                    ['key' => 'petugas_penghitung', 'label' => 'Petugas Penghitung'],
                    ['key' => 'keterangan', 'label' => 'Keterangan'],
                ],
            ],
            'intensi-misa' => [
                'model' => \App\Models\IntensiMisa::class,
                'title' => 'Pencatatan Intensi Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                    ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                    ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                    ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],
                    ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                    ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
                ],
            ],
            'intensi' => [
                'model' => \App\Models\IntensiMisa::class,
                'title' => 'Pencatatan Intensi Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                    ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                    ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                    ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],
                    ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                    ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
                ],
            ],
            'kegiatan' => [
                'model' => \App\Models\Kegiatan::class,
                'title' => 'Agenda Kegiatan Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'gambar', 'altKey' => 'foto', 'label' => 'Poster', 'isImage' => true],
                    ['key' => 'nama_kegiatan', 'altKey' => 'judul', 'label' => 'Nama Kegiatan', 'isPrimary' => true],
                    ['key' => 'kategori', 'label' => 'Kategori'],
                    ['key' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'isDate' => true],
                    ['key' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'isDate' => true],
                    ['key' => 'waktu', 'altKey' => 'jam', 'label' => 'Waktu / Jam', 'isTime' => true],
                    ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'keuangan' => [
                'model' => \App\Models\Keuangan::class,
                'title' => 'Kas & Transaksi Keuangan Paroki',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'tanggal', 'label' => 'Tanggal Transaksi', 'isDate' => true],
                    ['key' => 'jenis', 'altKey' => 'jenis_transaksi', 'label' => 'Jenis'],
                    ['key' => 'kategori', 'label' => 'Kategori Transaksi', 'isPrimary' => true],
                    ['key' => 'kode_coa', 'label' => 'Kode COA'],
                    ['key' => 'jumlah', 'altKey' => 'nominal', 'label' => 'Jumlah (Rp)'],
                    ['key' => 'penerima', 'label' => 'Penerima / Pihak Terkait'],
                    ['key' => 'status_approval', 'label' => 'Status Approval'],
                    ['key' => 'keterangan', 'label' => 'Keterangan'],
                ],
            ],
            'aset' => [
                'model' => \App\Models\Aset::class,
                'title' => 'Data Aset & Inventaris Paroki',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'kode_aset', 'label' => 'Kode Aset'],
                    ['key' => 'nama_aset', 'label' => 'Nama Barang / Aset', 'isPrimary' => true],
                    ['key' => 'kategori', 'label' => 'Kategori Aset'],
                    ['key' => 'jumlah', 'label' => 'Jumlah'],
                    ['key' => 'satuan', 'label' => 'Satuan'],
                    ['key' => 'kondisi', 'label' => 'Kondisi Fisik'],
                    ['key' => 'nilai_perolehan', 'label' => 'Nilai Perolehan (Rp)'],
                    ['key' => 'lokasi', 'label' => 'Lokasi / Ruangan'],
                    ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'],
                    ['key' => 'tanggal_perolehan', 'label' => 'Tgl Perolehan', 'isDate' => true],
                ],
            ],
            'surat-masuk' => [
                'model' => \App\Models\SuratMasuk::class,
                'title' => 'Surat Masuk',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true],
                    ['key' => 'pengirim', 'label' => 'Pengirim'],
                    ['key' => 'perihal', 'label' => 'Perihal'],
                    ['key' => 'tgl_surat', 'label' => 'Tanggal'],
                ],
            ],
            'surat-keluar' => [
                'model' => \App\Models\SuratKeluar::class,
                'title' => 'Surat Keluar',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true],
                    ['key' => 'tujuan', 'label' => 'Tujuan'],
                    ['key' => 'perihal', 'label' => 'Perihal'],
                    ['key' => 'tgl_surat', 'label' => 'Tanggal'],
                ],
            ],
            'arsip-digital' => [
                'model' => \App\Models\ArsipDigital::class,
                'title' => 'Arsip Digital',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'judul', 'altKey' => 'nama_dokumen', 'label' => 'Nama Dokumen / Judul', 'isPrimary' => true],
                    ['key' => 'nomor_arsip', 'label' => 'Nomor Arsip'],
                    ['key' => 'kategori_arsip', 'altKey' => 'kategori', 'label' => 'Kategori'],
                    ['key' => 'tanggal_arsip', 'altKey' => 'tgl_arsip', 'label' => 'Tanggal Arsip', 'isDate' => true],
                    ['key' => 'hak_akses', 'altKey' => 'privacy_level', 'label' => 'Hak Akses'],
                ],
            ],
            'rapat-notulen' => [
                'model' => \App\Models\Rapat::class,
                'title' => 'Rapat & Notulen',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true],
                    ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true],
                    ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true],
                    ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'],
                    ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'rapat' => [
                'model' => \App\Models\Rapat::class,
                'title' => 'Rapat & Notulen',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true],
                    ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true],
                    ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true],
                    ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'],
                    ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'master-uskup' => [
                'model' => \App\Models\MasterUskup::class,
                'title' => 'Daftar Uskup',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_uskup', 'label' => 'Nama Uskup', 'isPrimary' => true],
                    ['key' => 'keuskupan', 'label' => 'Keuskupan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'master-pastor' => [
                'model' => \App\Models\MasterPastor::class,
                'title' => 'Daftar Pastor / Imam',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_pastor', 'label' => 'Nama Pastor', 'isPrimary' => true],
                    ['key' => 'gelar_depan', 'label' => 'Gelar'],
                    ['key' => 'jabatan', 'label' => 'Jabatan'],
                    ['key' => 'jenis_imam', 'label' => 'Jenis Imam'],
                    ['key' => 'ordo', 'label' => 'Ordo'],
                    ['key' => 'keuskupan', 'label' => 'Keuskupan'],
                    ['key' => 'no_hp', 'label' => 'Kontak / WA'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'riwayat-pastor' => [
                'model' => \App\Models\RiwayatPastorParoki::class,
                'title' => 'Riwayat Pastor Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                    ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                    ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                    ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                    ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
                ],
            ],
            'riwayat_pastor_paroki' => [
                'model' => \App\Models\RiwayatPastorParoki::class,
                'title' => 'Riwayat Pastor Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                    ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                    ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                    ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                    ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
                ],
            ],
            'direktori-dpp' => [
                'model' => \App\Models\DirektoriDpp::class,
                'title' => 'Direktori Dewan Pastoral Paroki (DPP)',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'label' => 'Nama Pengurus', 'isPrimary' => true],
                    ['key' => 'jabatan', 'label' => 'Jabatan'],
                    ['key' => 'seksi', 'label' => 'Seksi / Bidang'],
                    ['key' => 'periode', 'label' => 'Periode'],
                    ['key' => 'no_hp', 'label' => 'Kontak / WA'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                ],
            ],
            'direktori-katekis' => [
                'model' => \App\Models\DirektoriKatekis::class,
                'title' => 'Direktori Katekis',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'label' => 'Nama Katekis', 'isPrimary' => true],
                    ['key' => 'jenis_katekis', 'label' => 'Jenis Katekis'],
                    ['key' => 'wilayah_pelayanan', 'label' => 'Wilayah Pelayanan'],
                    ['key' => 'sertifikasi', 'label' => 'Sertifikasi'],
                    ['key' => 'no_hp', 'label' => 'Kontak / WA'],
                    ['key' => 'status_aktif', 'label' => 'Status'],
                ],
            ],
            'direktori-misdinar' => [
                'model' => \App\Models\DirektoriMisdinar::class,
                'title' => 'Direktori Misdinar',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_lengkap', 'label' => 'Nama Anggota', 'isPrimary' => true],
                    ['key' => 'stasi', 'label' => 'Stasi / Kapela'],
                    ['key' => 'status_aktif', 'label' => 'Status'],
                ],
            ],
            'kronik' => [
                'model' => \App\Models\KronikParoki::class,
                'title' => 'Kronik Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto_utama', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'judul_kronik', 'label' => 'Judul Kronik', 'isPrimary' => true],
                    ['key' => 'tanggal_peristiwa', 'label' => 'Tanggal Peristiwa'],
                    ['key' => 'kategori_kronik', 'label' => 'Kategori'],
                    ['key' => 'lokasi_peristiwa', 'label' => 'Lokasi'],
                    ['key' => 'penulis', 'label' => 'Penulis'],
                    ['key' => 'status_publish', 'label' => 'Status'],
                ],
            ],
            'kronik-paroki' => [
                'model' => \App\Models\KronikParoki::class,
                'title' => 'Kronik Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto_utama', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'judul_kronik', 'label' => 'Judul Kronik', 'isPrimary' => true],
                    ['key' => 'tanggal_peristiwa', 'label' => 'Tanggal Peristiwa'],
                    ['key' => 'kategori_kronik', 'label' => 'Kategori'],
                    ['key' => 'lokasi_peristiwa', 'label' => 'Lokasi'],
                    ['key' => 'penulis', 'label' => 'Penulis'],
                    ['key' => 'status_publish', 'label' => 'Status'],
                ],
            ],
            'kronik_paroki' => [
                'model' => \App\Models\KronikParoki::class,
                'title' => 'Kronik Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto_utama', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'judul_kronik', 'label' => 'Judul Kronik', 'isPrimary' => true],
                    ['key' => 'tanggal_peristiwa', 'label' => 'Tanggal Peristiwa'],
                    ['key' => 'kategori_kronik', 'label' => 'Kategori'],
                    ['key' => 'lokasi_peristiwa', 'label' => 'Lokasi'],
                    ['key' => 'penulis', 'label' => 'Penulis'],
                    ['key' => 'status_publish', 'label' => 'Status'],
                ],
            ],
            'jadwal-petugas-liturgi' => [
                'model' => \App\Models\JadwalPetugasLiturgi::class,
                'title' => 'Petugas Liturgi Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_petugas', 'label' => 'Nama Petugas', 'isPrimary' => true],
                    ['key' => 'jenis_tugas', 'label' => 'Jenis Tugas'],
                    ['key' => 'kelompok', 'label' => 'Kelompok / KUB / Lingkungan'],
                    ['key' => 'keterangan', 'label' => 'Keterangan'],
                ],
            ],
            'jadwal_petugas_liturgi' => [
                'model' => \App\Models\JadwalPetugasLiturgi::class,
                'title' => 'Petugas Liturgi Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_petugas', 'label' => 'Nama Petugas', 'isPrimary' => true],
                    ['key' => 'jenis_tugas', 'label' => 'Jenis Tugas'],
                    ['key' => 'kelompok', 'label' => 'Kelompok / KUB / Lingkungan'],
                    ['key' => 'keterangan', 'label' => 'Keterangan'],
                ],
            ],
            'petugas-liturgi' => [
                'model' => \App\Models\JadwalPetugasLiturgi::class,
                'title' => 'Petugas Liturgi Misa',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_petugas', 'label' => 'Nama Petugas', 'isPrimary' => true],
                    ['key' => 'jenis_tugas', 'label' => 'Jenis Tugas'],
                    ['key' => 'kelompok', 'label' => 'Kelompok / KUB / Lingkungan'],
                    ['key' => 'keterangan', 'label' => 'Keterangan'],
                ],
            ],
            'defunctorum' => [
                'model' => \App\Models\Defunctorum::class,
                'title' => 'Data Umat Meninggal (Defunctorum)',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Almarhum / Almarhumah', 'isPrimary' => true],
                    ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                    ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                    ['key' => 'tanggal_lahir', 'label' => 'Tgl Lahir', 'isDate' => true],
                    ['key' => 'tanggal_meninggal', 'label' => 'Tgl Meninggal', 'isDate' => true],
                    ['key' => 'tanggal_pemakaman', 'label' => 'Tgl Pemakaman', 'isDate' => true],
                    ['key' => 'tempat_pemakaman', 'label' => 'Lokasi Makam'],
                    ['key' => 'dilayani_oleh', 'label' => 'Dilayani Oleh'],
                    ['key' => 'penyebab_kematian', 'label' => 'Penyebab'],
                ],
            ],
            'umat-meninggal' => [
                'model' => \App\Models\Defunctorum::class,
                'title' => 'Data Umat Meninggal (Defunctorum)',
                'has_import' => true,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Almarhum / Almarhumah', 'isPrimary' => true],
                    ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                    ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                    ['key' => 'tanggal_lahir', 'label' => 'Tgl Lahir', 'isDate' => true],
                    ['key' => 'tanggal_meninggal', 'label' => 'Tgl Meninggal', 'isDate' => true],
                    ['key' => 'tanggal_pemakaman', 'label' => 'Tgl Pemakaman', 'isDate' => true],
                    ['key' => 'tempat_pemakaman', 'label' => 'Lokasi Makam'],
                    ['key' => 'dilayani_oleh', 'label' => 'Dilayani Oleh'],
                    ['key' => 'penyebab_kematian', 'label' => 'Penyebab'],
                ],
            ],
            'sambutan-pastor' => [
                'model' => \App\Models\SambutanPastor::class,
                'title' => 'Sambutan Pastor Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto_pastor', 'altKey' => 'foto', 'label' => 'Foto Pastor', 'isImage' => true],
                    ['key' => 'nama_pastor', 'label' => 'Nama Pastor', 'isPrimary' => true],
                    ['key' => 'jabatan_pastor', 'altKey' => 'jabatan', 'label' => 'Jabatan'],
                    ['key' => 'judul_sambutan', 'label' => 'Judul Sambutan'],
                    ['key' => 'tanggal_sambutan', 'label' => 'Tanggal', 'isDate' => true],
                    ['key' => 'status_publish', 'label' => 'Status'],
                ],
            ],
            'sambutan_pastor' => [
                'model' => \App\Models\SambutanPastor::class,
                'title' => 'Sambutan Pastor Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto_pastor', 'altKey' => 'foto', 'label' => 'Foto Pastor', 'isImage' => true],
                    ['key' => 'nama_pastor', 'label' => 'Nama Pastor', 'isPrimary' => true],
                    ['key' => 'jabatan_pastor', 'altKey' => 'jabatan', 'label' => 'Jabatan'],
                    ['key' => 'judul_sambutan', 'label' => 'Judul Sambutan'],
                    ['key' => 'tanggal_sambutan', 'label' => 'Tanggal', 'isDate' => true],
                    ['key' => 'status_publish', 'label' => 'Status'],
                ],
            ],
            'metode-pembayaran' => [
                'model' => \App\Models\MetodePembayaran::class,
                'title' => 'Metode Pembayaran & QRIS',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'logo_bank', 'label' => 'Logo Bank', 'isImage' => true],
                    ['key' => 'nama_bank', 'label' => 'Nama Bank / Channel', 'isPrimary' => true],
                    ['key' => 'nomor_rekening', 'label' => 'No. Rekening / VA'],
                    ['key' => 'atas_nama', 'label' => 'Atas Nama (Pemilik)'],
                    ['key' => 'gambar_qris', 'label' => 'QRIS', 'isImage' => true],
                    ['key' => 'tipe', 'label' => 'Tipe'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'metode_pembayaran' => [
                'model' => \App\Models\MetodePembayaran::class,
                'title' => 'Metode Pembayaran & QRIS',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'logo_bank', 'label' => 'Logo Bank', 'isImage' => true],
                    ['key' => 'nama_bank', 'label' => 'Nama Bank / Channel', 'isPrimary' => true],
                    ['key' => 'nomor_rekening', 'label' => 'No. Rekening / VA'],
                    ['key' => 'atas_nama', 'label' => 'Atas Nama (Pemilik)'],
                    ['key' => 'gambar_qris', 'label' => 'QRIS', 'isImage' => true],
                    ['key' => 'tipe', 'label' => 'Tipe'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'slider' => [
                'model' => \App\Models\SliderBanner::class,
                'title' => 'Banner Slider Beranda',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'gambar', 'label' => 'Gambar Banner', 'isImage' => true],
                    ['key' => 'judul', 'label' => 'Judul Slider', 'isPrimary' => true],
                    ['key' => 'subjudul', 'label' => 'Subjudul'],
                    ['key' => 'link_url', 'label' => 'Link Tujuan'],
                    ['key' => 'tombol_teks', 'label' => 'Teks Tombol'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'slider-banner' => [
                'model' => \App\Models\SliderBanner::class,
                'title' => 'Banner Slider Beranda',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'gambar', 'label' => 'Gambar Banner', 'isImage' => true],
                    ['key' => 'judul', 'label' => 'Judul Slider', 'isPrimary' => true],
                    ['key' => 'subjudul', 'label' => 'Subjudul'],
                    ['key' => 'link_url', 'label' => 'Link Tujuan'],
                    ['key' => 'tombol_teks', 'label' => 'Teks Tombol'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'slider_banner' => [
                'model' => \App\Models\SliderBanner::class,
                'title' => 'Banner Slider Beranda',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'gambar', 'label' => 'Gambar Banner', 'isImage' => true],
                    ['key' => 'judul', 'label' => 'Judul Slider', 'isPrimary' => true],
                    ['key' => 'subjudul', 'label' => 'Subjudul'],
                    ['key' => 'link_url', 'label' => 'Link Tujuan'],
                    ['key' => 'tombol_teks', 'label' => 'Teks Tombol'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'peran-kategorial' => [
                'model' => \App\Models\PeranKategorial::class,
                'title' => 'Peran Kategorial',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_peran', 'label' => 'Nama Peran', 'isPrimary' => true],
                    ['key' => 'kode_peran', 'label' => 'Kode'],
                    ['key' => 'kategori', 'label' => 'Kategori'],
                    ['key' => 'nama_koordinator', 'label' => 'Koordinator'],
                    ['key' => 'lokasi_kegiatan', 'label' => 'Lokasi'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'anggota-kategorial' => [
                'model' => \App\Models\AnggotaKategorial::class,
                'title' => 'Anggota Kategorial',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_anggota', 'label' => 'Nama Anggota', 'isPrimary' => true],
                    ['key' => 'peran_nama', 'relation' => 'peranKategorial', 'relationKey' => 'nama_peran', 'label' => 'Peran Kategorial'],
                    ['key' => 'jabatan_dalam_kelompok', 'label' => 'Jabatan'],
                    ['key' => 'tanggal_bergabung', 'label' => 'Tanggal Bergabung'],
                    ['key' => 'status_keanggotaan', 'label' => 'Status'],
                ],
            ],
            'kategori-konten' => [
                'model' => \App\Models\KategoriKonten::class,
                'title' => 'Kategori Konten & Artikel',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                    ['key' => 'slug', 'label' => 'Slug URL'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'kategori_konten' => [
                'model' => \App\Models\KategoriKonten::class,
                'title' => 'Kategori Konten & Artikel',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                    ['key' => 'slug', 'label' => 'Slug URL'],
                    ['key' => 'total_konten', 'label' => 'Jumlah Konten'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                    ['key' => 'urutan', 'label' => 'Urutan'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'konten' => [
                'model' => \App\Models\Konten::class,
                'title' => 'Konten Website',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'gambar', 'altKey' => 'foto', 'label' => 'Gambar', 'isImage' => true],
                    ['key' => 'judul', 'label' => 'Judul Artikel', 'isPrimary' => true],
                    ['key' => 'kategori', 'label' => 'Kategori'],
                    ['key' => 'penulis', 'label' => 'Penulis'],
                    ['key' => 'status_publish', 'label' => 'Status'],
                    ['key' => 'views', 'label' => 'Dibaca'],
                    ['key' => 'created_at', 'label' => 'Tanggal', 'isDate' => true],
                ],
            ],
            'komentar-artikel' => [
                'model' => \App\Models\KomentarArtikel::class,
                'title' => 'Komentar & Diskusi Artikel',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama Pengirim', 'isPrimary' => true],
                    ['key' => 'konten_judul', 'relation' => 'konten', 'relationKey' => 'judul', 'label' => 'Artikel / Konten'],
                    ['key' => 'pesan', 'label' => 'Isi Komentar'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'status', 'label' => 'Status Moderasi'],
                    ['key' => 'has_bad_words', 'label' => 'Kata Kasar', 'isBoolean' => true],
                    ['key' => 'bad_words_found', 'label' => 'Kata Terdeteksi'],
                    ['key' => 'created_at', 'label' => 'Waktu Kirim', 'isDate' => true],
                ],
            ],
            'komentar_artikel' => [
                'model' => \App\Models\KomentarArtikel::class,
                'title' => 'Komentar & Diskusi Artikel',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama', 'label' => 'Nama Pengirim', 'isPrimary' => true],
                    ['key' => 'konten_judul', 'relation' => 'konten', 'relationKey' => 'judul', 'label' => 'Artikel / Konten'],
                    ['key' => 'pesan', 'label' => 'Isi Komentar'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'status', 'label' => 'Status Moderasi'],
                    ['key' => 'has_bad_words', 'label' => 'Kata Kasar', 'isBoolean' => true],
                    ['key' => 'bad_words_found', 'label' => 'Kata Terdeteksi'],
                    ['key' => 'created_at', 'label' => 'Waktu Kirim', 'isDate' => true],
                ],
            ],
            'pengumuman' => [
                'model' => \App\Models\Pengumuman::class,
                'title' => 'Pengumuman Paroki',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'judul', 'label' => 'Judul Pengumuman', 'isPrimary' => true],
                    ['key' => 'tgl_tayang', 'label' => 'Tanggal'],
                ],
            ],
            'renungan' => [
                'model' => \App\Models\Renungan::class,
                'title' => 'Renungan Harian',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'judul', 'label' => 'Judul Renungan', 'isPrimary' => true],
                    ['key' => 'bacaan_kitab_suci', 'label' => 'Bacaan'],
                    ['key' => 'tanggal', 'label' => 'Tanggal'],
                ],
            ],
            'galeri' => [
                'model' => \App\Models\Galeri::class,
                'title' => 'Galeri Foto & Dokumentasi',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'gambar', 'label' => 'Foto / Gambar', 'isImage' => true],
                    ['key' => 'judul', 'altKey' => 'judul_foto', 'label' => 'Judul Dokumentasi', 'isPrimary' => true],
                    ['key' => 'album', 'altKey' => 'kategori', 'label' => 'Album / Kegiatan'],
                    ['key' => 'tipe', 'label' => 'Tipe Media'],
                    ['key' => 'tanggal', 'label' => 'Tanggal Kegiatan', 'isDate' => true],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'download' => [
                'model' => \App\Models\Downloads::class,
                'title' => 'Download Dokumen',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_file', 'label' => 'Nama File', 'isPrimary' => true],
                    ['key' => 'kategori', 'label' => 'Kategori'],
                ],
            ],
            'role' => [
                'model' => \App\Models\Role::class,
                'title' => 'Role & Hak Akses (RBAC)',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'nama_role', 'label' => 'Nama Role / Peran', 'isPrimary' => true],
                    ['key' => 'slug', 'label' => 'Kode / Slug'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi Wewenang & Tanggung Jawab'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'roles' => [
                'model' => \App\Models\Role::class,
                'title' => 'Role & Hak Akses (RBAC)',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'nama_role', 'label' => 'Nama Role / Peran', 'isPrimary' => true],
                    ['key' => 'slug', 'label' => 'Kode / Slug'],
                    ['key' => 'deskripsi', 'label' => 'Deskripsi Wewenang & Tanggung Jawab'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
            ],
            'user' => [
                'model' => \App\Models\User::class,
                'title' => 'Manajemen User',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                    ['key' => 'nama_lengkap', 'altKey' => 'name', 'label' => 'Pengguna', 'isPrimary' => true],
                    ['key' => 'username', 'label' => 'Username'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'role_nama', 'relation' => 'role', 'relationKey' => 'nama_role', 'label' => 'Peran / Role'],
                    ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah'],
                    ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'],
                    ['key' => 'kub_nama', 'relation' => 'kub', 'relationKey' => 'nama_kub', 'label' => 'KUB'],
                ],
            ],
            'lapak-produk' => [
                'model' => \App\Models\LapakProduk::class,
                'title' => 'Lapak & Usaha UMKM Umat',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'foto', 'label' => 'Foto Produk', 'isImage' => true],
                    ['key' => 'nama_produk', 'label' => 'Nama Produk', 'isPrimary' => true],
                    ['key' => 'kategori', 'label' => 'Kategori'],
                    ['key' => 'harga', 'label' => 'Harga (Rp)'],
                    ['key' => 'stok', 'label' => 'Stok'],
                    ['key' => 'penjual', 'label' => 'Penjual (Umat)'],
                    ['key' => 'no_wa', 'label' => 'WhatsApp / Kontak'],
                    ['key' => 'status_approval', 'label' => 'Status Moderasi'],
                ],
            ],
            'provinsi' => [
                'model' => \App\Models\Provinsi::class,
                'title' => 'Data Provinsi',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_provinsi', 'label' => 'Nama Provinsi', 'isPrimary' => true],
                    ['key' => 'kode_provinsi', 'label' => 'Kode'],
                    ['key' => 'kabupatens', 'label' => 'Kabupaten / Kota', 'isRelationLink' => true, 'relation' => 'kabupatens', 'linkTo' => 'kabupaten', 'filterParam' => 'provinsi_id', 'icon' => 'fa-city', 'color' => 'blue'],
                ],
            ],
            'kabupaten' => [
                'model' => \App\Models\Kabupaten::class,
                'title' => 'Data Kabupaten / Kota',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kabupaten', 'label' => 'Nama Kabupaten / Kota', 'isPrimary' => true],
                    ['key' => 'tipe', 'label' => 'Tipe'],
                    ['key' => 'kode_kabupaten', 'label' => 'Kode'],
                    ['key' => 'provinsi_nama', 'relation' => 'provinsi', 'relationKey' => 'nama_provinsi', 'label' => 'Provinsi'],
                    ['key' => 'kecamatans', 'label' => 'Kecamatan', 'isRelationLink' => true, 'relation' => 'kecamatans', 'linkTo' => 'kecamatan', 'filterParam' => 'kabupaten_id', 'icon' => 'fa-building-columns', 'color' => 'emerald'],
                ],
            ],
            'kecamatan' => [
                'model' => \App\Models\Kecamatan::class,
                'title' => 'Data Kecamatan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_kecamatan', 'label' => 'Nama Kecamatan', 'isPrimary' => true],
                    ['key' => 'kode_kecamatan', 'label' => 'Kode'],
                    ['key' => 'kabupaten_nama', 'relation' => 'kabupaten', 'relationKey' => 'nama_kabupaten', 'label' => 'Kabupaten / Kota'],
                    ['key' => 'desas', 'label' => 'Desa / Kelurahan', 'isRelationLink' => true, 'relation' => 'desas', 'linkTo' => 'desa-kelurahan', 'filterParam' => 'kecamatan_id', 'icon' => 'fa-tree-city', 'color' => 'purple'],
                ],
            ],
            'desa-kelurahan' => [
                'model' => \App\Models\DesaKelurahan::class,
                'title' => 'Data Desa / Kelurahan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                    ['key' => 'kode_desa', 'label' => 'Kode'],
                    ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
                ],
            ],
            'desa' => [
                'model' => \App\Models\DesaKelurahan::class,
                'title' => 'Data Desa / Kelurahan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                    ['key' => 'kode_desa', 'label' => 'Kode'],
                    ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
                ],
            ],
            'kelurahan' => [
                'model' => \App\Models\DesaKelurahan::class,
                'title' => 'Data Desa / Kelurahan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                    ['key' => 'kode_desa', 'label' => 'Kode'],
                    ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
                ],
            ],
            'desa_kelurahan' => [
                'model' => \App\Models\DesaKelurahan::class,
                'title' => 'Data Desa / Kelurahan',
                'has_import' => false,
                'has_export' => true,
                'has_pdf' => true,
                'columns' => [
                    ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                    ['key' => 'kode_desa', 'label' => 'Kode'],
                    ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
                ],
            ],
            'pengaturan-aplikasi' => [
                'model' => \App\Models\PengaturanAplikasi::class,
                'title' => 'Pengaturan Aplikasi',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'nama_aplikasi', 'label' => 'Nama Aplikasi', 'isPrimary' => true],
                    ['key' => 'email', 'label' => 'Email'],
                ],
            ],
            'security-settings' => [
                'model' => \App\Models\SecuritySettings::class,
                'title' => 'Security Settings',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'setting_key', 'label' => 'Kunci Pengaturan', 'isPrimary' => true],
                    ['key' => 'setting_value', 'label' => 'Nilai'],
                ],
            ],
            'backup-database' => [
                'model' => \App\Models\BackupDatabase::class,
                'title' => 'Backup Database',
                'has_import' => false,
                'has_export' => false,
                'has_pdf' => false,
                'columns' => [
                    ['key' => 'nama_file', 'label' => 'File Backup', 'isPrimary' => true],
                    ['key' => 'created_at', 'label' => 'Tanggal Backup'],
                ],
            ],
        ];
    }

    private function normalizeIuranPayload(array $data): array
    {
        if (isset($data['id_kk'])) {
            $data['id_kk'] = !empty($data['id_kk']) ? (int) $data['id_kk'] : null;
        } elseif (isset($data['kk_id'])) {
            $data['id_kk'] = !empty($data['kk_id']) ? (int) $data['kk_id'] : null;
        }

        if (isset($data['jenis_iuran_id'])) {
            $data['jenis_iuran_id'] = !empty($data['jenis_iuran_id']) ? (int) $data['jenis_iuran_id'] : null;
        }

        if (isset($data['total_jumlah']) && !isset($data['jumlah'])) {
            $data['jumlah'] = (float) $data['total_jumlah'];
        } elseif (isset($data['jumlah'])) {
            $data['jumlah'] = (float) $data['jumlah'];
        } else {
            $data['jumlah'] = 0.00;
        }

        // Map month names to 2-digit numbers: varchar(2)
        $rawBulan = $data['bulan'] ?? $data['bulan_lunas'] ?? date('m');
        $monthMap = [
            'januari' => '01', 'january' => '01', 'jan' => '01', '1' => '01', '01' => '01',
            'februari' => '02', 'february' => '02', 'feb' => '02', '2' => '02', '02' => '02',
            'maret' => '03', 'march' => '03', 'mar' => '03', '3' => '03', '03' => '03',
            'april' => '04', 'apr' => '04', '4' => '04', '04' => '04',
            'mei' => '05', 'may' => '05', '5' => '05', '05' => '05',
            'juni' => '06', 'june' => '06', 'jun' => '06', '6' => '06', '06' => '06',
            'juli' => '07', 'july' => '07', 'jul' => '07', '7' => '07', '07' => '07',
            'agustus' => '08', 'august' => '08', 'aug' => '08', 'ags' => '08', '8' => '08', '08' => '08',
            'september' => '09', 'sep' => '09', '9' => '09', '09' => '09',
            'oktober' => '10', 'october' => '10', 'okt' => '10', 'oct' => '10', '10' => '10',
            'november' => '11', 'nov' => '11', '11' => '11',
            'desember' => '12', 'december' => '12', 'des' => '12', 'dec' => '12', '12' => '12',
        ];
        $cleanBulanKey = strtolower(trim((string) $rawBulan));
        $data['bulan'] = $monthMap[$cleanBulanKey] ?? str_pad((string) (int) $rawBulan, 2, '0', STR_PAD_LEFT);
        if ($data['bulan'] === '00' || strlen($data['bulan']) > 2) {
            $data['bulan'] = date('m');
        }

        // Normalize status: enum('lunas','belum_lunas')
        $rawStatus = strtolower(trim((string) ($data['status'] ?? $data['status_bayar'] ?? 'lunas')));
        if (str_contains($rawStatus, 'belum') || str_contains($rawStatus, 'pending') || str_contains($rawStatus, 'cicil')) {
            $data['status'] = 'belum_lunas';
        } else {
            $data['status'] = 'lunas';
        }

        // Normalize metode_bayar: enum('tunai','transfer','lainnya')
        $rawMetode = strtolower(trim((string) ($data['metode_bayar'] ?? $data['metode_pembayaran'] ?? 'tunai')));
        if (str_contains($rawMetode, 'trans') || str_contains($rawMetode, 'bank') || str_contains($rawMetode, 'qris')) {
            $data['metode_bayar'] = 'transfer';
        } elseif (str_contains($rawMetode, 'tunai') || str_contains($rawMetode, 'cash') || str_contains($rawMetode, 'kolektor')) {
            $data['metode_bayar'] = 'tunai';
        } else {
            $data['metode_bayar'] = 'lainnya';
        }

        if (empty($data['tanggal_bayar'])) {
            $data['tanggal_bayar'] = date('Y-m-d');
        }
        if (empty($data['tahun'])) {
            $data['tahun'] = (int) date('Y');
        }
        if (auth()->id()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }
}
