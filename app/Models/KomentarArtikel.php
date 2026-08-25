<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class KomentarArtikel extends Model
{
    protected $table = 'komentar_artikel';

    protected $fillable = [
        'konten_id',
        'parent_id',
        'nama',
        'email',
        'pesan',
        'status',
        'has_bad_words',
        'bad_words_found',
        'is_admin_reply',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'has_bad_words' => 'boolean',
            'is_admin_reply' => 'boolean',
        ];
    }

    /**
     * Self-healing schema check to ensure table exists in database.
     */
    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('komentar_artikel')) {
            Schema::create('komentar_artikel', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('konten_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('nama', 100);
                $table->string('email', 100)->nullable();
                $table->text('pesan');
                $table->string('status', 20)->default('Disetujui')->index();
                $table->boolean('has_bad_words')->default(false);
                $table->string('bad_words_found')->nullable();
                $table->boolean('is_admin_reply')->default(false);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });

            \Illuminate\Support\Facades\Cache::forget('schema_columns_komentar_artikel');
        }
    }

    /**
     * Artikel / Konten terkait.
     */
    public function konten(): BelongsTo
    {
        return $this->belongsTo(Konten::class, 'konten_id');
    }

    /**
     * Komentar Induk jika merupakan balasan.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(KomentarArtikel::class, 'parent_id');
    }

    /**
     * Balasan-balasan untuk komentar ini.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(KomentarArtikel::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope komentar yang telah disetujui untuk ditampilkan ke publik.
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'Disetujui');
    }

    /**
     * Scope komentar yang memerlukan tinjauan moderasi.
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'Menunggu');
    }
}
