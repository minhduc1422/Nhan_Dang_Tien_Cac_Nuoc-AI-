<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Kết nối MySQL
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "money"; 

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Kết nối thất bại: " . $conn->connect_error]));
}

// Lấy dữ liệu từ bảng `transactions`
$sql = "SELECT * FROM transactions";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();
echo json_encode(["status" => "success", "data" => $data]);
?>
