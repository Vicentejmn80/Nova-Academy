<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'teacher_id',
        'course_id',
        'plan_block_id',
        'title',
        'description',
        'max_score',
        'weight_percentage',
        'due_date',
        'type',
        'is_homework',
        'nee_type',
        'nee_adaptation',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date:Y-m-d',
            'max_score' => 'integer',
            'weight_percentage' => 'float',
            'is_homework' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(Tarea::class, 'actividad_id');
    }
}
