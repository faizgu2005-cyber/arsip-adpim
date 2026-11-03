<?php

use yii\db\Migration;

/**
 * Membuat tabel pengguna, arsip, dan percobaan login, lalu mengisi akun admin awal.
 */
class m260101_000001_buat_tabel_awal extends Migration
{
    public function safeUp()
    {
        $opsi = $this->db->driverName === 'mysql'
            ? 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            : null;

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $opsi);

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

        $this->createTable('{{%login_gagal}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(45)->notNull(),
            'waktu' => $this->integer()->notNull(),
        ], $opsi);
        $this->createIndex('idx_login_gagal_ip', '{{%login_gagal}}', ['ip', 'waktu']);

        // Akun awal: admin / admin123. Segera ganti: php yii user/ganti-password
        $this->insert('{{%user}}', [
            'username' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii::$app->security->generateRandomString(32),
            'created_at' => time(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%login_gagal}}');
        $this->dropTable('{{%arsip}}');
        $this->dropTable('{{%user}}');
    }
}
