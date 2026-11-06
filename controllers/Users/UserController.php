<?php

declare(strict_types=1);

namespace App\Controllers\Users;

use App\Controllers\Base\Controller;
use App\Dtos\Users\UserFormDto;
use App\Dtos\Users\UserQuery;
use App\Enums\Users\UserRole;
use App\Services\Users\UserService;
use App\Utilities\Http\Request;

/** Admin user management. */
final class UserController extends Controller
{
    public function index(Request $request): void
    {
        $this->render($request, 'users/index', [
            'title' => 'Users',
            'page' => (new UserService())->list(UserQuery::fromQuery($request->queryAll())),
            'filters' => $request->queryAll(),
            'roles' => UserRole::options(),
        ]);
    }

    public function create(Request $request): void
    {
        $this->render($request, 'users/form', ['title' => 'New user', 'user' => null, 'roles' => UserRole::options()]);
    }

    public function edit(Request $request): void
    {
        $user = (new UserService())->findByCode($request->param('code'));
        $this->render($request, 'users/form', ['title' => 'Edit user', 'user' => $user, 'roles' => UserRole::options()]);
    }

    public function store(Request $request): void
    {
        (new UserService())->create(UserFormDto::fromInput($request->all(), true));
        $this->done('User created.', '/users', 201);
    }

    public function update(Request $request): void
    {
        (new UserService())->update($this->user(), $request->param('code'), UserFormDto::fromInput($request->all(), false));
        $this->done('User updated.', '/users');
    }

    public function toggle(Request $request): void
    {
        $user = (new UserService())->toggleActive($this->user(), $request->param('code'));
        $this->done($user->isActive ? 'User activated.' : 'User deactivated.');
    }
}
