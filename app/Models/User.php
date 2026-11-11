<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function company()
    {
        return $this->hasOne(Company::class); 
    }

    /**
     * Staff record if this user is a staff member of a seller company.
     */
    public function sellerStaff()
    {
        return $this->hasOne(SellerStaff::class);
    }

    /**
     * Field sales agent profile if this user is an onboarded field agent.
     */
    public function fieldAgent()
    {
        return $this->hasOne(FieldAgent::class, 'user_id');
    }

    public function isFieldAgent(): bool
    {
        return $this->fieldAgent()->exists();
    }

    public function isAdmin(): bool
    {
        return $this->id === 1 || str_contains(strtolower($this->email ?? ''), 'admin');
    }

    public function isSeller(): bool
    {
        if ($this->isFieldAgent() && !$this->company()->exists() && !$this->sellerStaff()->exists() && !str_contains(strtolower($this->email ?? ''), 'seller')) {
            return false;
        }

        return $this->company()->exists() 
            || $this->sellerStaff()->exists()
            || str_contains(strtolower($this->email ?? ''), 'seller') 
            || !$this->isAdmin();
    }

    /**
     * Check if the user is a seller owner (owns a company).
     */
    public function isSellerOwner(): bool
    {
        return $this->company()->exists();
    }

    /**
     * Get the company this user belongs to (either as owner or staff).
     */
    public function resolveSellerCompany(): ?Company
    {
        if ($this->company) {
            return $this->company;
        }

        $staff = $this->sellerStaff()->with('company')->first();
        return $staff?->company;
    }

    /**
     * Check if user has a seller permission (works for owner, staff, and admin).
     */
    public function hasSellerPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Seller owners have all permissions
        if ($this->isSellerOwner()) {
            return true;
        }

        $staff = $this->sellerStaff()->with(['role.permissions', 'overridePermissions.permission'])->first();
        return $staff ? $staff->hasPermission($permission) : false;
    }

    public function canAccessFilament(): bool
    {
        if ($this->isFieldAgent() && !$this->isAdmin()) {
            return false;
        }

        return true;
    }
}
