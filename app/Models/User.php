<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // [DAGDAG] Import para sa Sanctum Tokens

#[Fillable(['name', 'email','role','created_by', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; // [DAGDAG] Isinama ang HasApiTokens dito

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- ELOQUENT RELATIONSHIPS FOR THE EXAM ---

    // Para sa Project Manager: Ang user na ito ay pwedeng makagawa ng maraming projects.
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    // Para sa Team Member: Ang user na ito ay pwedeng ma-assign sa maraming projects sa pamamagitan ng pivot table.
    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    // Para sa Team Member: Ang user na ito ay pwedeng magkaroon ng maraming tasks na naka-assign sa kanya.
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
}