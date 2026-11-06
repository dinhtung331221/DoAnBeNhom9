<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "kiemtralai";
    private $conn;

    // Hàm khởi tạo – tự động kết nối
    public function __construct() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
        if ($this->conn->connect_error) {
            die("Kết nối thất bại: " . $this->conn->connect_error);
        }
        // Thiết lập bộ mã UTF-8
        $this->conn->set_charset("utf8");
    }

    // Truy vấn SELECT – trả về mảng dữ liệu
    public function select($sql) {
        $result = $this->conn->query($sql);
        $data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Thực thi lệnh INSERT, UPDATE, DELETE
    public function execute($sql) {
        return $this->conn->query($sql);
    }

    // Hàm ngắt kết nối
    public function close() {
        $this->conn->close();
    }
}
?>
