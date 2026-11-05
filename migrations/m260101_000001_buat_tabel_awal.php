<?php

use yii\db\Migration;

/**
 * Membuat tabel pengguna, arsip, dan percobaan login, lalu mengisi akun admin awal.
 */
class m260101_000001_buat_tabel_awal extends Migration
{
    public function safeUp()
    {
        // MySQL tidak membatalkan CREATE TABLE saat transaksi migrasi gagal.
        // Periksa semua tabel lebih dahulu agar migrasi bisa dilanjutkan setelah
        // upload database lama atau kegagalan migrasi sebagian.
        $required = [
            'user' => ['id', 'username', 'password_hash', 'auth_key', 'created_at'],
            'arsip' => ['id', 'jenis', 'judul', 'tanggal', 'keterangan', 'file_name', 'file_stored', 'created_at', 'updated_at'],
            'login_gagal' => ['id', 'ip', 'waktu'],
        ];
        $exists = [];
        foreach ($required as $name => $columns) {
            $table = $this->db->schema->getTableSchema('{{%' . $name . '}}', true);
            $exists[$name] = $table !== null;
            if ($table !== null) {
                $missing = array_diff($columns, array_keys($table->columns));
                if ($missing) {
                    throw new \RuntimeException('Tabel ' . $name . ' sudah ada tetapi kolom belum lengkap: ' . implode(', ', $missing) . '. Sesuaikan struktur sebelum melanjutkan migrasi.');
                }
                echo '    Tabel ' . $name . ' sudah ada; data dipertahankan.' . PHP_EOL;
            }
        }

        $opsi = $this->db->driverName === 'mysql'
            ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            : null;

        if (!$exists['user']) {
            $this->createTable('{{%user}}', [
                'id' => $this->primaryKey(),
                'username' => $this->string(50)->notNull()->unique(),
                'password_hash' => $this->string(255)->notNull(),
                'auth_key' => $this->string(32)->notNull(),
                'created_at' => $this->integer()->notNull(),
            ], $opsi);
        }

        if (!$exists['arsip']) {
            $this->createTable('{{%arsip}}', [
                'id' => $this->primaryKey(),
                'jenis' => $this->string(10)->notNull(),
                'judul' => $this->string(500)->notNull(),
                'tanggal' => $this->date()->notNull(),
                'keterangan' => $this->string(10)->notNull(),
                'file_name' => $this->string(255)->null(),
                'file_stored' => $this->string(64)->null(),
                'created_at' => $this->integer()->notNull(),
                'updated_at' => $this->integer()->notNull(),
            ], $opsi);
            $this->createIndex('idx_arsip_jenis_tanggal', '{{%arsip}}', ['jenis', 'tanggal']);
        }

        if (!$exists['login_gagal']) {
            $this->createTable('{{%login_gagal}}', [
                'id' => $this->primaryKey(),
                'ip' => $this->string(45)->notNull(),
                'waktu' => $this->integer()->notNull(),
            ], $opsi);
            $this->createIndex('idx_login_gagal_ip', '{{%login_gagal}}', ['ip', 'waktu']);
        }

        // Akun awal: admin / admin123. Segera ganti: php yii user/ganti-password
        // Jangan menambah akun default pada database pengguna yang sudah ada.
        if (!$exists['user']) {
            $this->insert('{{%user}}', [
                'username' => 'admin',
                'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
                'auth_key' => Yii::$app->security->generateRandomString(32),
                'created_at' => time(),
            ]);
        }
    }

    public function safeDown()
    {
        // Tabel mungkin berasal dari impor database, bukan dibuat migrasi ini.
        echo "Migrasi awal tidak dapat dibatalkan otomatis karena dapat menghapus data yang sudah ada.\n";
        return false;
    }
}
