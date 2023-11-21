<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class RegisterService
{
    public function registerUser(array $validatedData)
    {
        $existingUser = $this->findExistingUserByEmail($validatedData['email']);

        // email已存在
        if ($existingUser) {
            return $this->handleUserPassword($existingUser, $validatedData);
        }

        // email不存在
        return $this->createNewUser($validatedData);
    }

    protected function findExistingUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    protected function handleUserPassword(User $existingUser, array $data)
    {
        // 如果用户已存在，但密碼欄位為空，那更新密码
        if (empty($existingUser['password'])) {
            $existingUser->update([
                'password' => Hash::make($data['password']),
            ]);
            return ['message' => config('success_messages.GOOGLE_USER_PASSWORD_UPDATE'), 'status' => Response::HTTP_OK];
        }

        // 如果用户已存在，密碼欄位也已存在
        return ['error' => config('error_messages.EMAIL_HAS_REGISTERED'), 'status' => Response::HTTP_CONFLICT];
    }


    protected function createNewUser(array $data, $user = User::ROLE_USER)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $user,
        ]);

        // 觸發注册事件
        event(new Registered($user));

        // 返回新建用户的回應
        return ['message' => config('success_messages.REGISTER_SUCCESS'), 'status' => Response::HTTP_CREATED, 'user' => $user];
    }
}
