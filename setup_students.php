<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
  $c = new mysqli('localhost', 'root', '', 'ksm_database');
  $c->query("CREATE TABLE IF NOT EXISTS users_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    class VARCHAR(100),
    rollNo VARCHAR(100),
    parentName VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
  echo "users_students OK\n";
  
  $c->query("CREATE TABLE IF NOT EXISTS users_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50),
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
  echo "users_staff OK\n";
} catch (Exception $e) {
  echo $e->getMessage();
}
