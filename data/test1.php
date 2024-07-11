<?php
// 数据库文件路径
$dbPath = 'D:/phpstudy_pro/WWW/xingweiindustrial.com/data/pbootcms.db';

try {
    // 创建 PDO 实例来连接 SQLite 数据库
    $pdo = new PDO('sqlite:' . $dbPath);

    // 设置 PDO 错误模式为异常
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 获取所有表名
    $query = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $query->fetchAll(PDO::FETCH_COLUMN);

    $output = '';

    // 遍历所有表，生成 SQL
    foreach ($tables as $table) {
        $output .= "DROP TABLE IF EXISTS \"$table\";\n";
        $create = $pdo->query("SELECT sql FROM sqlite_master WHERE name='$table'")->fetchColumn();
        $output .= $create . ";\n\n";

        // 导出数据
        $rows = $pdo->query("SELECT * FROM \"$table\"");
        $rows->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $cols = array_keys($row);
            $colNames = '"' . implode('", "', $cols) . '"';
            $colValues = array_map(function ($val) use ($pdo) {
                return $pdo->quote($val);
            }, array_values($row));
            $values = implode(', ', $colValues);
            $output .= "INSERT INTO \"$table\" ($colNames) VALUES ($values);\n";
        }
        $output .= "\n";
    }

    // 保存到文件
    $filename = 'database_backup.txt';
    file_put_contents($filename, $output);
    echo "Database has been successfully backed up to $filename";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
