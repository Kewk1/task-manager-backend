<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    // I-secure natin ang mga columns na pwedeng lagyan ng data
    protected $fillable = ['title', 'description', 'created_by'];

    /**
     * Ang Project ay pagmamay-ari ng isang Project Manager (User).
     * Kabaligtaran ito ng createdProjects() sa User model natin.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ang Project ay may maraming Team Members (Users) sa pamamagitan ng pivot table natin.
     * Kabaligtaran ito ng assignedProjects() sa User model.
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    /**
     * Ang Project ay naglalaman ng maraming Tasks.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}