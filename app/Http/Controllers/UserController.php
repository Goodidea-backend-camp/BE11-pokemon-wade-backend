<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\S3Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group User
 * Operations related to users.
 * 
 * @authenticated
 */
class UserController extends Controller
{

    /**
     * 使用者個人資訊
     * 
     * 同時驗證cookie的token有效性，
     * 失敗的話要清除token。
     * 
     * @response {
     *  "name": "John Doe",
     *  "photo": "https://example.com/photo.jpg",
     *  "email": "johndoe@example.com"
     * }
     * 
     * @response 404 {
     *  "error": "User not found"
     * }
     * 
     * @response 401 {
     *  "error": "驗證失敗"
     * }
     */
    public function show()
    {
        $user = Auth::user();

        $userData = User::select('name', 'photo', 'email')->where('id', $user->id)->first();

        return response()->json($userData);
    }


     /**
     * 更新使用者資訊。
     *
     * 此方法允許更新使用者的基本資訊。如果請求中包含用戶照片，它將生成一個預簽名的URL，
     * 以便用戶的瀏覽器可以直接上傳圖片到S3。
     *
     * @bodyParam name string optional 新的用戶名稱。示例：John Doe
     * @bodyParam email string optional 新的電子郵件地址。示例：john.doe@example.com
     * @bodyParam userPhoto file optional 用戶的新照片。應該是一個圖像文件。
     *
     * @response {
     *   "presignedUrl": "https://example.com/presigned-url",
     *   "fileDestination": "userPhotos/1234567890.jpg"
     * }
     */
    public function update(UserRequest $request,S3Service $s3Service)
    {
        $user = Auth::user();
        $validatedData = $request->validated();
        $user->update($validatedData);
    
        // 假設是上傳檔案，這裡就要去組裝presign URL
        if ($request->hasFile('userPhoto')) {
            $file = $request->file('userPhoto');
            $responseData = $s3Service->handleFileUpload($user, $file);

            return response()->json($responseData);
        }
        }
    }


  
