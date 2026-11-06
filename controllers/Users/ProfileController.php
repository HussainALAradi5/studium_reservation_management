<?php

declare(strict_types=1);

namespace App\Controllers\Users;

use App\Controllers\Base\Controller;
use App\Dtos\Users\ChangePasswordDto;
use App\Dtos\Users\ProfileDto;
use App\Services\Users\UserService;
use App\Utilities\Http\Request;

final class ProfileController extends Controller
{
    public function show(Request $request): void
    {
        $this->render($request, 'users/profile', ['title' => 'My profile', 'user' => $this->user()]);
    }

    public function update(Request $request): void
    {
        (new UserService())->updateProfile($this->user(), ProfileDto::fromInput($request->all()));
        $this->done('Profile updated.');
    }

    public function changePassword(Request $request): void
    {
        (new UserService())->changePassword($this->user(), ChangePasswordDto::fromInput($request->all()));
        $this->done('Password changed.');
    }
}
