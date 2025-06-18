<?php
// Helper untuk operasi database
require_once __DIR__ . '/../config/database.php';

// Fungsi generic SELECT
function db_select($query, $params = [])
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("SELECT Error: " . $e->getMessage());
        return [];
    }
}

// Fungsi generic INSERT/UPDATE/DELETE
function db_execute($query, $params = [])
{
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Execute Error: " . $e->getMessage());
        return false;
    }
}

// Fungsi pencarian sederhana berdasarkan kolom
function db_search($table, $keyword, $columns = [])
{
    global $pdo;

    if (empty($columns)) return [];

    $likeConditions = array_map(function ($col) {
        return "$col LIKE ?";
    }, $columns);

    $sql = "SELECT * FROM $table WHERE " . implode(' OR ', $likeConditions);
    $params = array_fill(0, count($columns), "%$keyword%");

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Search Error: " . $e->getMessage());
        return [];
    }
}

// Fungsi backup data dari satu tabel
function backup_table($table)
{
    global $pdo;
    try {
        $rows = db_select("SELECT * FROM $table");
        if (empty($rows)) return "-- Tidak ada data di tabel $table\n";

        $backup = "-- Backup data dari tabel $table\n";

        foreach ($rows as $row) {
            $cols = array_map(function ($val) use ($pdo) {
                return $pdo->quote($val);
            }, array_values($row));

            $backup .= "INSERT INTO `$table` VALUES (" . implode(',', $cols) . ");\n";
        }

        return $backup;
    } catch (PDOException $e) {
        error_log("Backup error: " . $e->getMessage());
        return "-- Gagal backup tabel $table\n";
    }
}

// Pagination helper
function db_paginate($query, $page = 1, $perPage = 10, $params = [])
{
    $offset = ($page - 1) * $perPage;
    $query .= " LIMIT $perPage OFFSET $offset";
    return db_select($query, $params);
}

// Simple validation
function validate_data($data, $required = [])
{
    $errors = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[] = "$field wajib diisi.";
        }
    }
    return $errors;
}

// Health check
function db_health_check()
{
    try {
        global $pdo;
        return $pdo->query("SELECT 1")->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}
