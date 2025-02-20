    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('pengaduan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('kategori_id')->constrained('kategoris')->onDelete('cascade');
                $table->foreignId('lokasi_id')->constrained('lokasis')->onDelete('cascade');
                $table->string('judul');
                $table->text('isi_pengaduan');
                $table->string('foto')->nullable();
                $table->enum('status', ['proses', 'diterima', 'selesai'])->default('proses');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('pengaduan');
        }
    };
