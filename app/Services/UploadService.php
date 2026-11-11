<?php

namespace App\Services;

use RuntimeException;

class UploadService
{
    public function productImage($file)
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Không thể tải hình ảnh lên.');
        }
        if ($file['size'] > app_config('upload_max_bytes')) {
            throw new RuntimeException('Hình ảnh vượt quá dung lượng cho phép.');
        }
        $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
        }
        $relative = 'uploads/products/' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
        $destination = BASE_PATH . '/public/' . $relative;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Không thể lưu hình ảnh.');
        }
        return $relative;
    }
}
