<?php
namespace App\Services;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class GoogleLoginService
{
    public function handleGoogleUser($googleUser)
    {
        // 查找或創建用户
        $user = $this->findOrCreateUser($googleUser);

        // 更新用户的 Google ID
        $this->updateGoogleId($user, $googleUser);

        // 生成JWT
        return JWTAuth::fromUser($user);
    }

    // 查找或創建用户的邏輯
    protected function findOrCreateUser($googleUser)
    {
        return User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
            ]
        );
    }

    // 更新Google ID的邏輯
    protected function updateGoogleId(User $user, $googleUser)
    {
        if (empty($user->google_id)) {
            $user->google_id = $googleUser->getId();
            $user->save();
        }
    }

}
