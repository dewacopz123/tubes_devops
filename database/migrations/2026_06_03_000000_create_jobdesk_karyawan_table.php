<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jobdesk_karyawan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jobdesk_id')->constrained('jobdesks')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['jobdesk_id', 'karyawan_id']);
        });

        $jobdeskAssignments = DB::table('jobdesks')
            ->select('id as jobdesk_id', 'karyawan_id')
            ->whereNotNull('karyawan_id')
            ->get();

        if ($jobdeskAssignments->isNotEmpty()) {
            $insertData = $jobdeskAssignments->map(function ($assignment) {
                return [
                    'jobdesk_id' => $assignment->jobdesk_id,
                    'karyawan_id' => $assignment->karyawan_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            DB::table('jobdesk_karyawan')->insert($insertData);
        }

        Schema::table('jobdesks', function (Blueprint $table) {
            $table->dropForeign(['karyawan_id']);
            $table->dropColumn('karyawan_id');
        });
    }

    public function down(): void
    {
        Schema::table('jobdesks', function (Blueprint $table) {
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->cascadeOnDelete();
        });

        $assignments = DB::table('jobdesk_karyawan')
            ->select('jobdesk_id', 'karyawan_id')
            ->orderBy('id')
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('jobdesks')
                ->where('id', $assignment->jobdesk_id)
                ->update(['karyawan_id' => $assignment->karyawan_id]);
        }

        Schema::dropIfExists('jobdesk_karyawan');
    }
};
