<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    // I-secure natin ang mga columns para sa mass assignment
    protected $fillable = ['project_id', 'assigned_to', 'title', 'description', 'status'];

    //Ang Task ay nabibilang sa isang partikular na Project.
    public function project(): BelongsTo
    {
     return $this->belongsTo(Project::class);
    }
    //Ang Task ay naka-assign sa isang partikular na Team Member (User).
    //Kabaligtaran ito ng tasks() relation na isinulat natin sa User.php.
    
    public function developer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
}