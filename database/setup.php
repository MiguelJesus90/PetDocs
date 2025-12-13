<?php
// database/setup.php

// Define the path for the SQLite database file
$dbFile = __DIR__ . '/petdocs.sqlite';

try {
    // Create a new PDO instance for SQLite
    $pdo = new PDO('sqlite:' . $dbFile);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Enable foreign key constraints
    $pdo->exec('PRAGMA foreign_keys = ON;');

    // SQL to create the 'pets' table
    $sqlPets = "
    CREATE TABLE IF NOT EXISTS pets (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      species TEXT,
      breed TEXT,
      birth_date TEXT,
      owner_name TEXT,
      photo TEXT,
      created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    );";

    // SQL to create the 'documents' table
    $sqlDocuments = "
    CREATE TABLE IF NOT EXISTS documents (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      pet_id INTEGER NOT NULL,
      document_type TEXT NOT NULL,
      file_name TEXT NOT NULL,
      file_path TEXT NOT NULL,
      file_size INTEGER NOT NULL,
      notes TEXT,
      upload_date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (pet_id) REFERENCES pets (id) ON DELETE CASCADE
    );";

    // Execute the SQL statements
    $pdo->exec($sqlPets);
    $pdo->exec($sqlDocuments);

    echo "Database and tables created successfully at: " . $dbFile . PHP_EOL;

} catch (PDOException $e) {
    // Handle any errors
    echo "Error creating database: " . $e->getMessage() . PHP_EOL;
    exit(1); // Exit with an error code
}
