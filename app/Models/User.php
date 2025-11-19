<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use function PHPSTORM_META\map;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens,SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'avatar',
        'email',
        'bio',
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

    public function follow(User $user) {
        if(!$this->isFollowing($user->id)) {
            Follow::create([
                'user_id' => auth()->id(),
                'following_id' => $user->id
            ]);
        }
    }

    public function unfollow(User $user) {
        Follow::where('user_id', auth()->id())->where('following_id', $user->id)->delete();
    }

    public function getAvatarLinkAttribute() {
        return ($this->avatar == null) ? 'avatar.png' : $this->avatar;
    }


    public function isFollowing($user_id) {
        return $this->following()->where('users.id', $user_id)->exists();
    }

    public function following() {
        return $this->hasManyThrough(User::class, Follow::class, 'user_id', 'id', 'id', 'following_id');
    }
    public function getFollowingCountAttribute() {
        return $this->following()->count();
    }

    public function followers() {
        return $this->hasManyThrough(User::class, Follow::class, 'following_id', 'id', 'id', 'user_id');
    }

    public function getFollowersCountAttribute() {
        return $this->followers()->count();
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }
    
    static public function public_fields(Array $fields = []) {
        return array_merge(['id','username','name','avatar'],$fields);
    }

    public function likes() {

        return $this->hasMany(Like::class);
    }

    public function getLikesCountAttribute() {
        return $this->likes()->count();
    }

}
