<?php

namespace App;

use Exception;
class Session
{
    private static $csrfTokens = [];

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function getUser(): ?array
    {
        return self::get('user');
    }

    public static function register(string $username, string $password)
    {
        try {
            $db = Database::getInstance();
            // Check if user already exists
            $existingUser = $db->fetchOne("select * from users where username = :username", [
                'username' => $username,
            ]);

            if ($existingUser) {
                throw new Exception("User already exists");
            }
            else {
                Log::info("Registering new user: $username");
                
                $db->query("insert into users (username, password) values (:username, :password)", [
                    'username' => $username,
                    'password' => password_hash($password, PASSWORD_DEFAULT)
                ]);
                http_response_code(200);
                return json_encode(['success' => true]);
            }
        } catch (Exception $e) {
            Log::error("Registration failed for user $username: " . $e->getMessage());
            http_response_code(401);
            return json_encode(['success' => false, 'message' => "Registration failed: " . $e->getMessage()]);
        }
    }

    public static function login(string $username, $password): string|bool
    {
        try {
            $user = Database::getInstance()->fetchOne("select * from users where username = :username", [
                'username' => $username,
            ]);

            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Invalid username or password");
            }
            
            self::set('user', [
                'username' => $user['username'],
            ]);
            return json_encode(['success' => true]);
            
        } catch (Exception $e) {
            http_response_code(401);
            return json_encode(['success' => false, 'message' => "Login failed: " . $e->getMessage()]);
        }
    }

    public static function setCsrfToken(string $formName): void
    {
        $token = bin2hex(random_bytes(32));
        self::$csrfTokens[$formName] = $token;
    }

    public static function getCsrfToken(string $formName): ?string
    {
        return self::$csrfTokens[$formName] ?? null;
    }

    public static function validateCsrfToken(string $formName, string $token): bool
    {
        return isset(self::$csrfTokens[$formName]) && hash_equals(self::$csrfTokens[$formName], $token);
    }
}