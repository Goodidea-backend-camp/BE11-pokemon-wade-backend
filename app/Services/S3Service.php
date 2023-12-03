<?php

namespace App\Services;

use App\Models\User;
use Aws\S3\S3Client;

class S3Service
{
    protected $s3Client;

    public function __construct()
    {
        $this->s3Client = $this->createS3ClientObject();
    }

    public function handleFileUpload(User $user, $file)
    {
        // 產生檔案路徑並保存用戶照片
        $filePath = $this->getFilePathAndSaveUserPhoto($user, $file);
        // 取得檔案類型
        $filetype = $file->getClientMimeType();
    
        // 生成prsigned URL
        $presignedUrl = $this->generatePresignedUrl($filePath, $filetype);
    
        return [
            'presignedUrl' => $presignedUrl,
            'fileDestination' => $user->photo 
        ];     
    }

    protected function getFilePathAndSaveUserPhoto(User $user, $file)
    {
        $filename = time() . '.' . $file->extension();
        $filePath = 'userPhotos/' . $filename;
        $userPhotoFilePath = $this->getFullS3Url($filePath);

        $user->photo = $userPhotoFilePath;
        $user->save();

        return $filePath; // 返回檔案路徑，用於生成預簽名 URL
    }


    protected function getFullS3Url($filePath)
    {
        // 生成完整的 S3 URL
        $baseS3Url = config('filesystems.disks.s3.base_s3_url');
        return $baseS3Url . $filePath;
    }


    protected function createS3ClientObject()
    {
        return new S3Client([
            'version' => 'latest',
            'region' => config('filesystems.disks.s3.region'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    // 生成一個預簽名的URL，以便上傳文件到S3。
     
    //  此方法返回一個預簽名的URL，該URL允許客戶端直接將文件上傳到S3，而無需通過服務器。
    protected function generatePresignedUrl($filePath, $filetype)
    {
        $cmd = $this->s3Client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $filePath,
            'ContentType' => $filetype,
        ]);

        $requestObj = $this->s3Client->createPresignedRequest($cmd, '+10 minutes');
        return (string) $requestObj->getUri();
    }
}
