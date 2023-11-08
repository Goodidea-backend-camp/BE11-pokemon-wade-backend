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
            return $this->handleExistingUser($existingUser, $validatedData);
        }

        // email不存在
        return $this->createNewUser($validatedData);
    }

    protected function findExistingUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    protected function handleExistingUser(User $existingUser, array $data)
    {
        // 如果用户已存在，并且提供了密码，那更新密码
        if (empty($existingUser['password'])) {
            $existingUser->update([
                'password' => Hash::make($data['password']),
            ]);
            return ['message' => config('success_messages.GOOGLE_USER_PASSWORD_UPDATE'), 'status' => Response::HTTP_OK];
        }

        // 如果用户已存在，但没有提供密码（或密碼为空），返回錯誤
        return ['message' => config('error_messages.EMAIL_HAS_REGISTERED'), 'status' => Response::HTTP_CONFLICT];
    }


    protected function createNewUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        // 觸發注册事件
        event(new Registered($user));

        // 返回新建用户的回應
        return ['message' => config('success_messages.REGISTER_SUCCESSFULLY'), 'status' => Response::HTTP_CREATED, 'user' => $user];
    }
}
