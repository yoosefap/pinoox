<?php

/**
 * ***  *  *     *  ****  ****  *    *
 *   *  *  * *   *  *  *  *  *   *  *
 * ***  *  *  *  *  *  *  *  *    *
 *      *  *   * *  *  *  *  *   *  *
 *      *  *    **  ****  ****  *    *
 *
 * @author   Pinoox
 * @link https://www.pinoox.com
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace Pinoox\Model;

use Illuminate\Validation\Rule;
use Pinoox\Component\Database\Model;
use Pinoox\Model\Scope\AppScope;
use Pinoox\Portal\App\App;
use Pinoox\Portal\Hash;
use Pinoox\Portal\Url;

class UserModel extends Model
{

    const ACTIVE = 'active';
    const INACTIVE = 'inactive';
    const SUSPEND = 'suspend';
    const PENDING = 'pending';

    protected $table = 'pincore_user';
    public $incrementing = true;
    public $primaryKey = 'user_id';
    public $timestamps = true;
    private $defaultAvatarLink = null;

    protected $fillable = [
        'session_id',
        'avatar_id',
        'app',
        'fname',
        'lname',
        'username',
        'password',
        'group_key',
        'email',
        'mobile',
        'status',
    ];

    protected $appends = ['full_name', 'avatar'];

    protected $hidden = [
        'password', 'session_id', 'app'
    ];

    public static function hashPassword($password)
    {
        return Hash::make($password);
    }

    public static function updatePassword($user_id, $new_password)
    {
        $hashed_password = self::hashPassword($new_password);
        self::where('user_id', $user_id)->update(['password' => $hashed_password]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (UserModel $user) {
            $user->app = $user->app ?: self::getPackage();
            $user->status = $user->status ?: self::ACTIVE;
            $user->password = self::hashPassword($user->password);
        });

        static::updating(function (UserModel $user) {
            if ($user->isDirty('password')) {
                $user->password = self::hashPassword($user->password);
            }
        });

        static::deleting(function (UserModel $user) {
            $user->file->delete();
        });
    }

    public function getAvatarAttribute()
    {
        $file = FileModel::where('file_id', $this->avatar_id)->first();
        if (!is_null($this->defaultAvatarLink)) {
            return [
                'file_id' => $this->avatar_id,
                'file_link' => Url::check($file?->file_link, $this->defaultAvatarLink),
                'thumb_link' => Url::check($file?->file_link, $this->defaultAvatarLink),
            ];
        } else if (empty($this->avatar_id)) {
            return null;
        } else {
            return [
                'file_id' => $this->avatar_id,
                'file_link' => $file?->file_link,
                'thumb_link' => $file?->thumb_link,
            ];
        }
    }

    public function setDefaultAvatarLink(string $imageLink): void
    {
        $this->defaultAvatarLink = $imageLink;
    }

    public function file(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FileModel::class, 'avatar_id', 'file_id');
    }

    public function getFullNameAttribute()
    {
        return $this->fname . ' ' . $this->lname;
    }

    public static function setPackage(string $package): void
    {
        App::set('transport.user', $package)->save();
        self::addAppGlobalScope();
    }

    public static function getPackage(): string
    {
        $package = App::get('transport.user');
        return $package ?? App::package();
    }

    protected static function booted()
    {
        self::addAppGlobalScope();
    }

    private static function addAppGlobalScope(): void
    {
        static::addGlobalScope('app', new AppScope(static::getPackage()));
    }

    public static function ruleUnique($column = null, $ignoreUserId = null)
    {
        $rule = Rule::unique(static::tableName(), $column)->where('app', static::getPackage());

        if (!is_null($ignoreUserId)) {
            $rule = $rule->ignore($ignoreUserId, 'user_id');
        }

        return $rule;
    }
}
