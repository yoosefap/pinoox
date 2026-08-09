<?php

/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace App\com_pinoox_manager\Controller;

use Illuminate\Database\QueryException;
use Pinoox\Component\Http\JsonResponse;
use Pinoox\Component\Http\Request;
use Pinoox\Component\Kernel\Controller\ApiController;
use Pinoox\Component\Transport\TransportRuntime;
use Pinoox\Model\PermissionModel;
use Pinoox\Model\RoleModel;
use Pinoox\Model\UserModel;
use Pinoox\Portal\App\AppEngine;
use Pinoox\Portal\Auth;
use Pinoox\Portal\Config;
use Pinoox\Portal\Date;

class UserController extends ApiController
{
    private const STATUSES = [
        UserModel::ACTIVE,
        UserModel::INACTIVE,
        UserModel::SUSPEND,
        UserModel::PENDING,
    ];

    private const SORTABLE = [
        'user_id',
        'full_name',
        'username',
        'email',
        'status',
        'group_key',
        'created_at',
    ];

    public function get()
    {
        return self::getDataUser();
    }

    public function getOptions()
    {
        $options = Config::name('options')->get() ?? [];
        $options['lang'] = app()->lang();

        return $options;
    }

    public function getUsers(Request $request, $packageName)
    {
        $error = $this->guardPackage($packageName);
        if ($error) {
            return $error;
        }

        return $this->runForApp($packageName, function () use ($request, $packageName) {
            $meta = $this->accessMeta($packageName);
            $query = UserModel::query()->with('file');
            if (!empty($meta['has_roles'])) {
                $query->with('roles');
            }
            $q = trim((string) $request->get('q', ''));

            if ($q !== '') {
                $query->where(function ($builder) use ($q) {
                    $like = '%' . $q . '%';
                    $builder->where('fname', 'like', $like)
                        ->orWhere('lname', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like);

                    if (ctype_digit($q)) {
                        $builder->orWhere('user_id', (int) $q);
                    }
                });
            }

            $status = (string) $request->get('status', '');
            if ($status !== '' && in_array($status, self::STATUSES, true)) {
                $query->where('status', $status);
            }

            $groupKey = trim((string) $request->get('group', ''));
            if ($groupKey !== '') {
                $query->where('group_key', $groupKey);
            }

            $roleId = (int) $request->get('role', 0);
            if ($roleId > 0 && !empty($meta['has_roles'])) {
                $query->whereHas('roles', function ($builder) use ($roleId) {
                    $builder->where('role_id', $roleId);
                });
            }

            $sort = (string) $request->get('sort', 'user_id');
            if (!in_array($sort, self::SORTABLE, true)) {
                $sort = 'user_id';
            }

            $dir = strtolower((string) $request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            if ($sort === 'created_at') {
                $query->orderBy('created_at', $dir)->orderBy('user_id', $dir);
            } else {
                $query->flexibleOrderBy($sort, $dir)->orderBy('user_id', $dir);
            }

            $page = max(1, (int) $request->get('page', 1));
            $perPage = (int) $request->get('per_page', 10);
            if ($perPage < 5) {
                $perPage = 10;
            }
            if ($perPage > 50) {
                $perPage = 50;
            }

            $total = (int) $query->count();
            $items = $query->forPage($page, $perPage)->get()
                ->map(fn (UserModel $user) => $this->serializeUser($user))
                ->values()
                ->all();

            return $this->ok([
                'items' => $items,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'meta' => $meta,
            ]);
        });
    }

    public function createUser(Request $request, $packageName)
    {
        $error = $this->guardPackage($packageName);
        if ($error) {
            return $error;
        }

        return $this->runForApp($packageName, function () use ($request, $packageName) {
            $input = $this->validated($request, $this->userRules());
            $meta = $this->accessMeta($packageName);
            $payload = $this->userPayload($input, $meta, true);

            $user = UserModel::create($payload);
            $this->syncUserRoles($user, $input['role_ids'] ?? [], $meta['has_roles']);

            return $this->ok($this->serializeUser($user->fresh(['file', 'roles'])));
        });
    }

    public function updateUser(Request $request, $packageName, $userId)
    {
        $error = $this->guardPackage($packageName);
        if ($error) {
            return $error;
        }

        return $this->runForApp($packageName, function () use ($request, $packageName, $userId) {
            $user = UserModel::with(['file', 'roles'])->find((int) $userId);
            if (!$user) {
                return $this->fail('NOT_FOUND', 'manager.user_not_found', status: 404);
            }

            $input = $this->validated($request, $this->userRules((int) $user->user_id, false));
            $meta = $this->accessMeta($packageName);
            $payload = $this->userPayload($input, $meta, false);

            $user->fill($payload);
            $user->save();
            $this->syncUserRoles($user, $input['role_ids'] ?? null, $meta['has_roles']);

            return $this->ok($this->serializeUser($user->fresh(['file', 'roles'])));
        });
    }

    public function deleteUser(Request $request, $packageName, $userId)
    {
        $error = $this->guardPackage($packageName);
        if ($error) {
            return $error;
        }

        return $this->runForApp($packageName, function () use ($userId) {
            $user = UserModel::find((int) $userId);
            if (!$user) {
                return $this->fail('NOT_FOUND', 'manager.user_not_found', status: 404);
            }

            if ((int) $user->user_id === (int) Auth::id()) {
                return $this->fail('FORBIDDEN', 'manager.cannot_delete_self', status: 403);
            }

            if (!$user->delete()) {
                return $this->fail('DELETE_FAILED', 'manager.user_delete_failed');
            }

            return $this->ok(['user_id' => (int) $userId]);
        });
    }

    public function saveRolePermissions(Request $request, $packageName, $roleId)
    {
        $error = $this->guardPackage($packageName);
        if ($error) {
            return $error;
        }

        return $this->runForApp($packageName, function () use ($request, $packageName, $roleId) {
            $input = $this->validated($request, [
                'permission_ids' => 'nullable|array',
                'permission_ids.*' => 'integer',
            ]);

            try {
                $role = RoleModel::find((int) $roleId);
            } catch (QueryException) {
                return $this->fail('NOT_FOUND', 'manager.role_not_found', status: 404);
            }

            if (!$role) {
                return $this->fail('NOT_FOUND', 'manager.role_not_found', status: 404);
            }

            $ids = array_values(array_unique(array_map('intval', $input['permission_ids'] ?? [])));

            try {
                $valid = PermissionModel::whereIn('permission_id', $ids)->pluck('permission_id')->all();
                $role->permissions()->sync($valid);
            } catch (QueryException) {
                return $this->fail('SAVE_FAILED', 'manager.role_permission_failed');
            }

            return $this->ok($this->accessMeta($packageName));
        });
    }

    public function deleteAvatar()
    {
        $profile = Auth::removeAvatar(Auth::id());

        if ($profile === null) {
            return $this->deny('user.avatar_delete_failed');
        }

        return $this->message('user.avatar_deleted_successfully', $profile);
    }

    public function changeAvatar(Request $request)
    {
        if (!$request->files->has('avatar')) {
            return $this->deny('manager.invalid_request');
        }

        $this->validated($request, [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $result = Auth::changeAvatar(Auth::id(), $request->files->get('avatar'));

        if ($result === false) {
            return $this->deny('manager.invalid_request');
        }

        return $this->message('user.avatar_changed_successfully', $result);
    }

    public function changeInfo(Request $request)
    {
        $input = $this->validated($request, Auth::profileRules(Auth::id()));

        Auth::updateProfile(Auth::id(), $input);

        return $this->message('user.profile_updated_successfully');
    }

    public function changePassword(Request $request)
    {
        $inputs = $this->validated($request, Auth::passwordRules());

        $result = Auth::changePassword(Auth::id(), $inputs['old_password'], $inputs['new_password']);

        if ($result !== true) {
            $message = is_string($result) ? $result : 'user.err_old_password';

            return $this->deny($message);
        }

        return $this->message('user.password_changed_successfully');
    }

    public static function getDataUser()
    {
        return Auth::profile();
    }

    private function guardPackage(mixed $packageName): ?JsonResponse
    {
        if (!is_string($packageName) || $packageName === '' || !AppEngine::exists($packageName)) {
            return $this->deny('manager.request_not_valid');
        }

        return null;
    }

    private function runForApp(string $packageName, callable $callback): mixed
    {
        return TransportRuntime::runAs($packageName, function () use ($packageName, $callback) {
            UserModel::setPackage($packageName);
            RoleModel::setPackage($packageName);

            return $callback();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function userRules(?int $userId = null, bool $creating = true): array
    {
        $password = $creating ? 'required|string|min:5' : 'nullable|string|min:5';

        return [
            'fname' => 'nullable|string|max:100',
            'lname' => 'nullable|string|max:100',
            'email' => ['required', 'email', UserModel::ruleUnique('email', $userId)],
            'username' => ['required', 'alpha_dash:ascii', 'min:3', UserModel::ruleUnique('username', $userId)],
            'password' => $password,
            'mobile' => 'nullable|string|max:30',
            'status' => 'nullable|in:' . implode(',', self::STATUSES),
            'group_key' => 'nullable|string|max:80',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'integer',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $meta
     * @return array<string, mixed>
     */
    private function userPayload(array $input, array $meta, bool $creating): array
    {
        $status = $input['status'] ?? UserModel::ACTIVE;
        if (!in_array($status, self::STATUSES, true)) {
            $status = UserModel::ACTIVE;
        }

        $payload = [
            'fname' => trim((string) ($input['fname'] ?? '')),
            'lname' => trim((string) ($input['lname'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'username' => trim((string) ($input['username'] ?? '')),
            'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
            'status' => $status,
        ];

        if (!empty($input['password'])) {
            $payload['password'] = (string) $input['password'];
        }

        if (!empty($meta['has_groups'])) {
            $groupKey = trim((string) ($input['group_key'] ?? ''));
            $allowed = array_column($meta['groups'] ?? [], 'key');
            $payload['group_key'] = ($groupKey !== '' && in_array($groupKey, $allowed, true))
                ? $groupKey
                : null;
        } elseif ($creating) {
            $payload['group_key'] = null;
        }

        return $payload;
    }

    /**
     * @param list<int>|null $roleIds
     */
    private function syncUserRoles(UserModel $user, ?array $roleIds, bool $hasRoles): void
    {
        if (!$hasRoles || $roleIds === null) {
            return;
        }

        try {
            $ids = array_values(array_unique(array_map('intval', $roleIds)));
            $valid = RoleModel::whereIn('role_id', $ids)->pluck('role_id')->all();
            $user->roles()->sync($valid);
        } catch (QueryException) {
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(UserModel $user): array
    {
        $fullName = trim((string) ($user->full_name ?? ''));
        if ($fullName === '') {
            $fullName = trim((string) ($user->username ?: $user->email ?: ''));
        }

        $registerDate = $user->created_at?->format('Y-m-d H:i:s');
        $registerDateFa = $registerDate;

        try {
            if ($user->created_at) {
                $registerDateFa = Date::jalali($user->created_at)->format('Y/m/d');
            }
        } catch (\Throwable) {
            $registerDateFa = $registerDate;
        }

        $roles = [];
        try {
            $roles = $user->roles?->map(static function ($role) {
                return [
                    'role_id' => (int) $role->role_id,
                    'key' => $role->role_key,
                    'name' => $role->name ?: $role->role_key,
                ];
            })->values()->all() ?? [];
        } catch (QueryException) {
            $roles = [];
        }

        return [
            'user_id' => (int) $user->user_id,
            'fname' => $user->fname,
            'lname' => $user->lname,
            'full_name' => $fullName,
            'username' => $user->username,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'status' => $user->status,
            'group_key' => $user->group_key,
            'roles' => $roles,
            'register_date' => $registerDate,
            'register_date_fa' => $registerDateFa,
            'is_self' => (int) $user->user_id === (int) Auth::id(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function accessMeta(string $packageName): array
    {
        $config = [];

        try {
            $raw = AppEngine::config($packageName)->get('access');
            $config = is_array($raw) ? $raw : [];
        } catch (\Throwable) {
            $config = [];
        }

        $groups = [];
        foreach (($config['groups'] ?? []) as $key => $abilities) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            $groups[] = [
                'key' => $key,
                'label' => $key,
                'permissions' => is_array($abilities) ? array_values($abilities) : [],
            ];
        }

        $roles = [];
        try {
            $roles = RoleModel::with('permissions')->orderBy('name')->orderBy('role_key')->get()
                ->map(static function (RoleModel $role) {
                    return [
                        'role_id' => (int) $role->role_id,
                        'key' => $role->role_key,
                        'name' => $role->name ?: $role->role_key,
                        'description' => $role->description,
                        'permissions' => $role->permissions->map(static function ($permission) {
                            return [
                                'permission_id' => (int) $permission->permission_id,
                                'key' => $permission->permission_key,
                                'name' => $permission->name ?: $permission->permission_key,
                            ];
                        })->values()->all(),
                    ];
                })->all();
        } catch (QueryException) {
            $roles = [];
        }

        $permissions = [];
        try {
            $permissions = PermissionModel::orderBy('name')->orderBy('permission_key')->get()
                ->map(static function (PermissionModel $permission) {
                    return [
                        'permission_id' => (int) $permission->permission_id,
                        'key' => $permission->permission_key,
                        'name' => $permission->name ?: $permission->permission_key,
                        'description' => $permission->description,
                    ];
                })->all();
        } catch (QueryException) {
            $permissions = [];
        }

        return [
            'has_groups' => $groups !== [],
            'has_roles' => $roles !== [],
            'has_permissions' => $permissions !== [],
            'groups' => $groups,
            'roles' => $roles,
            'permissions' => $permissions,
            'statuses' => self::STATUSES,
        ];
    }
}
