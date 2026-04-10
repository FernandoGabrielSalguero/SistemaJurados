<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar tu archivo de conexión (usa $pdo)
require_once __DIR__ . '/config.php';

// Obtener el nombre de la base de datos actual
$baseDatos = $pdo->query("SELECT DATABASE()")->fetchColumn();

echo "<h1>📚 Estructura completa de la base de datos: <em>$baseDatos</em></h1>";

// Obtener todas las tablas
$tablesQuery = $pdo->query("SHOW TABLES");
$tables = $tablesQuery->fetchAll(PDO::FETCH_NUM);

foreach ($tables as $table) {
    $tableName = $table[0];
    echo "<h2>📄 Tabla: <strong>$tableName</strong></h2>";

    // Estructura de columnas
    $columnsQuery = $pdo->query("SHOW COLUMNS FROM `$tableName`");
    $columns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        foreach (['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'] as $campo) {
            $value = $column[$campo] ?? '';
            echo "<td>" . htmlspecialchars((string) $value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";

    // Foreign Keys (relaciones entre tablas)
    $relacionesQuery = $pdo->prepare("
        SELECT 
            COLUMN_NAME, 
            REFERENCED_TABLE_NAME, 
            REFERENCED_COLUMN_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = :db 
            AND TABLE_NAME = :tabla 
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $relacionesQuery->execute([
        ':db' => $baseDatos,
        ':tabla' => $tableName
    ]);

    $relaciones = $relacionesQuery->fetchAll(PDO::FETCH_ASSOC);
    if (count($relaciones) > 0) {
        echo "<strong>🔗 Relaciones:</strong><ul>";
        foreach ($relaciones as $rel) {
            echo "<li>Columna <code>{$rel['COLUMN_NAME']}</code> referencia a <code>{$rel['REFERENCED_TABLE_NAME']}.{$rel['REFERENCED_COLUMN_NAME']}</code></li>";
        }
        echo "</ul>";
    }
}
