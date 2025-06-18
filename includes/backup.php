<?php
// includes/backup.php
class DatabaseBackup {
    private $pdo;
    private $backup_dir;
    
    public function __construct($pdo, $backup_dir = '../backups/') {
        $this->pdo = $pdo;
        $this->backup_dir = $backup_dir;
        
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
    }
    
    /**
     * Create full database backup
     */
    public function createFullBackup() {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backup_dir . $filename;
        
        $tables = $this->getTables();
        $backup_content = '';
        
        foreach ($tables as $table) {
            $backup_content .= $this->backupTable($table);
        }
        
        file_put_contents($filepath, $backup_content);
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath)
        ];
    }
    
    /**
     * Backup single table
     */
    private function backupTable($table) {
        $output = "\n-- Table: {$table}\n";
        $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Get table structure
        $create_table = $this->pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $output .= $create_table['Create Table'] . ";\n\n";
        
        // Get table data
        $rows = $this->pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $output .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $row_values = array_map(function($value) {
                    return $value === null ? 'NULL' : $this->pdo->quote($value);
                }, $row);
                $values[] = '(' . implode(', ', $row_values) . ')';
            }
            
            $output .= implode(",\n", $values) . ";\n\n";
        }
        
        return $output;
    }
    
    /**
     * Get all tables
     */
    private function getTables() {
        $tables = [];
        $result = $this->pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    /**
     * Restore from backup
     */
    public function restoreBackup($backup_file) {
        if (!file_exists($backup_file)) {
            return ['success' => false, 'error' => 'Backup file not found'];
        }
        
        $sql = file_get_contents($backup_file);
        
        try {
            $this->pdo->exec($sql);
            return ['success' => true, 'message' => 'Database restored successfully'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $backups = [];
        $files = glob($this->backup_dir . 'backup_*.sql');
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'filepath' => $file,
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return $backups;
    }
}
?>