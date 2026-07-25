<?php

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $name = config('session_name', 'pq_session');
        session_name($name);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }

    public static function attemptAdmin(string $email, string $password): bool
    {
        $stmt = Database::get()->prepare('SELECT * FROM admins WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $admin = $stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        Database::get()->prepare('UPDATE admins SET last_login_at = ? WHERE id = ?')
            ->execute([date('Y-m-d H:i:s'), $admin['id']]);
        log_activity('admin', (int)$admin['id'], 'login', 'Admin logged in');
        return true;
    }

    public static function attemptClient(string $email, string $password): bool
    {
        $stmt = Database::get()->prepare('SELECT * FROM clients WHERE email = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([strtolower(trim($email))]);
        $client = $stmt->fetch();
        if (!$client || !password_verify($password, $client['password_hash'])) {
            return false;
        }
        session_regenerate_id(true);
        $_SESSION['client_id'] = (int)$client['id'];
        $_SESSION['client_name'] = $client['name'];
        $_SESSION['client_email'] = $client['email'];
        Database::get()->prepare('UPDATE clients SET last_login_at = ? WHERE id = ?')
            ->execute([date('Y-m-d H:i:s'), $client['id']]);
        log_activity('client', (int)$client['id'], 'login', 'Client logged in');
        return true;
    }

    public static function admin(): ?array
    {
        if (empty($_SESSION['admin_id'])) {
            return null;
        }
        return [
            'id' => (int)$_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'] ?? '',
            'email' => $_SESSION['admin_email'] ?? '',
            'role' => $_SESSION['admin_role'] ?? 'admin',
        ];
    }

    public static function client(): ?array
    {
        if (empty($_SESSION['client_id'])) {
            return null;
        }
        return [
            'id' => (int)$_SESSION['client_id'],
            'name' => $_SESSION['client_name'] ?? '',
            'email' => $_SESSION['client_email'] ?? '',
        ];
    }

    public static function requireAdmin(): array
    {
        $admin = self::admin();
        if (!$admin) {
            flash('error', 'Please sign in to continue.');
            redirect('/admin/login.php');
        }
        return $admin;
    }

    public static function requireClient(): array
    {
        $client = self::client();
        if (!$client) {
            flash('error', 'Please sign in to track your tours.');
            redirect('/client/login.php');
        }
        return $client;
    }

    public static function logoutAdmin(): void
    {
        $id = $_SESSION['admin_id'] ?? null;
        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
        if ($id) {
            log_activity('admin', (int)$id, 'logout', 'Admin logged out');
        }
    }

    public static function logoutClient(): void
    {
        $id = $_SESSION['client_id'] ?? null;
        unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email']);
        if ($id) {
            log_activity('client', (int)$id, 'logout', 'Client logged out');
        }
    }

    public static function adminCount(): int
    {
        try {
            return (int)Database::get()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function createPasswordReset(string $type, string $email): ?string
    {
        $table = $type === 'admin' ? 'admins' : 'clients';
        $stmt = Database::get()->prepare("SELECT id, name, email FROM {$table} WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $user = $stmt->fetch();
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        Database::get()->prepare(
            'INSERT INTO password_resets (user_type, user_id, email, token, expires_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$type, $user['id'], $user['email'], hash('sha256', $token), $expires, date('Y-m-d H:i:s')]);

        return $token;
    }

    public static function consumePasswordReset(string $type, string $token, string $newPassword): bool
    {
        $hash = hash('sha256', $token);
        $stmt = Database::get()->prepare(
            'SELECT * FROM password_resets
             WHERE user_type = ? AND token = ? AND used_at IS NULL AND expires_at > ?
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$type, $hash, date('Y-m-d H:i:s')]);
        $row = $stmt->fetch();
        if (!$row) {
            return false;
        }

        $table = $type === 'admin' ? 'admins' : 'clients';
        $pwd = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::get()->prepare("UPDATE {$table} SET password_hash = ?, updated_at = ? WHERE id = ?")
            ->execute([$pwd, date('Y-m-d H:i:s'), $row['user_id']]);
        Database::get()->prepare('UPDATE password_resets SET used_at = ? WHERE id = ?')
            ->execute([date('Y-m-d H:i:s'), $row['id']]);

        log_activity($type, (int)$row['user_id'], 'password_reset', 'Password was reset');
        return true;
    }
}
