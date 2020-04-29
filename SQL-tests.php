<?php
/* Template Name: Admin page */
// Create connection
$conn = new mysqli("localhost", "root", "root", "wpdb");
// Check connection
if ($conn->connect_error) {
    echo fuck;
}

$sql = "SELECT id FROM wp_items";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"] . "<br>";
    }
} else {
    echo "0 results";
}
$conn->close();
?>