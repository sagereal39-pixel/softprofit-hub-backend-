<?php
$conn = new mysqli('sql204.infinityfree.com', 'if0_42228997', '272hWpoDp9qP1g', 'if0_42228997_affiliate_blog', 3306);
if ($conn->connect_error) {
  echo "Failed: " . $conn->connect_error;
} else {
  echo "Connected successfully!";
  $conn->close();
}
