<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_ETUDIANT = 'etudiant';
    public const ROLE_PROFESSEUR = 'professeur';
    public const ROLE_ENTREPRISE = 'entreprise';
    public const ROLE_JURY = 'jury';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_ETUDIANT,
        self::ROLE_PROFESSEUR,
        self::ROLE_ENTREPRISE,
        self::ROLE_JURY,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'telephone',
        'specialites',
        'bio',
        'disponible',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'disponible' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string|array $role): bool
    {
        return is_array($role) ? in_array($this->role, $role, true) : $this->role === $role;
    }

    public function isAdmin(): bool { return $this->role === self::ROLE_ADMIN; }
    public function isEtudiant(): bool { return $this->role === self::ROLE_ETUDIANT; }
    public function isProfesseur(): bool { return $this->role === self::ROLE_PROFESSEUR; }
    public function isEntreprise(): bool { return $this->role === self::ROLE_ENTREPRISE; }
    public function isJury(): bool { return $this->role === self::ROLE_JURY; }

    public function profilEtudiant(): HasOne
    {
        return $this->hasOne(ProfilEtudiant::class, 'user_id');
    }

    public function profilEntreprise(): HasOne
    {
        return $this->hasOne(ProfilEntreprise::class, 'user_id');
    }

    public function offres(): HasMany
    {
        return $this->hasMany(Offre::class, 'company_id');
    }

    public function codesOtp(): HasMany
    {
        return $this->hasMany(CodeOtp::class, 'user_id');
    }

    public function stagesEtudiant(): HasMany { return $this->hasMany(Stage::class, 'student_id'); }
    public function stagesEntreprise(): HasMany { return $this->hasMany(Stage::class, 'company_id'); }
    public function stagesTuteur(): HasMany { return $this->hasMany(Stage::class, 'tutor_id'); }
    public function candidatures(): HasMany { return $this->hasMany(Candidature::class, 'student_id'); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class, 'user_id')->latest(); }

    /* Aliases anglais conservés pour compatibilité interne */
    public function studentProfile(): HasOne { return $this->profilEtudiant(); }
    public function companyProfile(): HasOne { return $this->profilEntreprise(); }
    public function offers(): HasMany { return $this->offres(); }
    public function otpCodes(): HasMany { return $this->codesOtp(); }
}
