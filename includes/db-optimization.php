<?php
// includes/db-optimization.php
class QueryOptimizer {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Analyze slow queries
     */
    public function analyzeSlowQueries() {
        $sql = "SELECT * FROM information_schema.processlist WHERE time > 5";
        return $this->pdo->query($sql)->fetchAll();
    }
    
    /**
     * Get table sizes
     */
    public function getTableSizes() {
        $sql = "SELECT 
                    table_name,
                    round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
                ORDER BY (data_length + index_length) DESC";
        
        return $this->pdo->query($sql)->fetchAll();
    }
    
    /**
     * Check for missing indexes
     */
    public function suggestIndexes() {
        // This would analyze query patterns and suggest indexes
        // Implementation depends on query log analysis
    }
}
?>