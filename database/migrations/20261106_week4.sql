USE qr_table_ordering_db;

ALTER TABLE qr_ban
  ADD COLUMN IF NOT EXISTS ngay_het_han DATETIME NULL AFTER ngay_tao;

CREATE INDEX IF NOT EXISTS idx_users_role_status ON users (role, trang_thai);
CREATE INDEX IF NOT EXISTS idx_danhmuc_status ON danh_muc (trang_thai);
CREATE INDEX IF NOT EXISTS idx_ban_status ON ban_an (trang_thai);
