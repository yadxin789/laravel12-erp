<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'position',
        'signature',
        'last_seen_at',
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
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * 头像完整 URL：上传的走 storage，默认的走 public/avatars
     * 参考项目把这段逻辑散在 Blade 里复制了 3 份，这里收敛成访问器
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        return str_starts_with($this->avatar, 'defaults/')
            ? asset('avatars/' . $this->avatar)
            : asset('storage/avatars/' . $this->avatar);
    }

    /**
     * 头像首字母（无头像时的兜底展示）
     */
    public function getInitialAttribute(): string
    {
        return mb_strtoupper(mb_substr((string) $this->name, 0, 1)) ?: 'U';
    }

    /**
     * 是否使用默认头像
     */
    public function getIsDefaultAvatarAttribute(): bool
    {
        return !empty($this->avatar) && str_starts_with($this->avatar, 'defaults/');
    }

    /**
     * 300 秒内有活动视为在线
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subSeconds(300));
    }

    /**
     * 刷新活跃时间
     */
    public function touchLastSeen(): void
    {
        $this->forceFill(['last_seen_at' => now()])->saveQuietly();
    }
}
