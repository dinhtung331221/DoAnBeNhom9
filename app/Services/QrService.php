<?php

namespace App\Services;

use App\Core\Database;
use RuntimeException;

class QrService
{
    public function validate($token)
    {
        if (!is_string($token) || strlen($token) < 32 || strlen($token) > 255) {
            return null;
        }

        $statement = Database::connection()->prepare(
            "SELECT q.id AS qr_id, q.ban_id, q.ngay_het_han, b.ma_ban, b.ten_ban, b.khu_vuc, b.suc_chua, b.trang_thai AS trang_thai_ban
             FROM qr_ban q
             INNER JOIN ban_an b ON b.id = q.ban_id
             WHERE q.token_hash = :token_hash AND q.trang_thai = 'active'
               AND (q.ngay_het_han IS NULL OR q.ngay_het_han > NOW())
             LIMIT 1"
        );
        $statement->execute(array('token_hash' => hash('sha256', $token)));
        $table = $statement->fetch();
        if (!$table || $table['trang_thai_ban'] === 'maintenance') {
            return null;
        }
        return $table;
    }

    public function generate($tableId, $expiresAt = null)
    {
        $pdo = Database::connection();
        $statement = $pdo->prepare("SELECT id, ma_ban FROM ban_an WHERE id = :id AND trang_thai <> 'maintenance'");
        $statement->execute(array('id' => $tableId));
        $table = $statement->fetch();
        if (!$table) {
            throw new RuntimeException('Bàn không tồn tại hoặc đang ngừng sử dụng.');
        }

        $token = bin2hex(random_bytes(32));
        $menuUrl = url('menu?token=' . rawurlencode($token));
        $pdo->beginTransaction();
        try {
            $revoke = $pdo->prepare("UPDATE qr_ban SET trang_thai = 'revoked', ngay_thu_hoi = NOW() WHERE ban_id = :ban_id AND trang_thai = 'active'");
            $revoke->execute(array('ban_id' => $tableId));
            $insert = $pdo->prepare(
                "INSERT INTO qr_ban (ban_id, token_hash, qr_image, trang_thai, ngay_het_han)
                 VALUES (:ban_id, :token_hash, :qr_image, 'active', :ngay_het_han)"
            );
            $insert->execute(array(
                'ban_id' => $tableId,
                'token_hash' => hash('sha256', $token),
                'qr_image' => $menuUrl,
                'ngay_het_han' => $expiresAt ?: date('Y-m-d H:i:s', strtotime('+1 year')),
            ));
            $pdo->commit();
        } catch (\Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }

        return array('id' => (int) $pdo->lastInsertId(), 'url' => $menuUrl, 'token' => $token);
    }

    public function revoke($id)
    {
        $statement = Database::connection()->prepare("UPDATE qr_ban SET trang_thai = 'revoked', ngay_thu_hoi = NOW() WHERE id = :id");
        $statement->execute(array('id' => $id));
        return $statement->rowCount() > 0;
    }
}

