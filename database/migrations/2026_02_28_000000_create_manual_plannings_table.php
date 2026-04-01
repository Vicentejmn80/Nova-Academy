<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_plannings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            // Sin FK: la migración de subjects corre después (2026_03_01); subject_id es referencia lógica opcional.
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedSmallInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->json('sessions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_plannings');
    }
};
