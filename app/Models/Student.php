<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'student_id',
        'class',
        'section',
        'photo',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeByClass($query, $class)
    {
        return $query->where('class', $class);
    }

    public function scopeBySection($query, $section)
    {
        return $query->where('section', $section);
    }
}