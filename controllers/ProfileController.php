<?php

namespace controllers;

use core\Controller;
use core\DB;
use core\Session;
use Exception;

class ProfileController extends Controller
{
    private DB $db;
    private Session $session;

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session();
        $this->db = new DB('localhost', 'Layttle', 'Dubrov', '2004Dubrov');
    }

    public function actionIndex($username = null): void
    {
        $currentUser = $this->session->get('user');

        if (!$currentUser || !isset($currentUser['id'])) {
            header('Location: /users/login');
            exit;
        }

        if ($username === null) {
            $username = $currentUser['login'];
        }
    }

        private function getUserDataByLogin(mixed $username)
    {
        if (empty($username) || strlen($username) < 3) {
            return null; // Некоректний логін
        }

        $result = $this->db->selectOne('users', ['login' => $username]);
        return $result ?: null;
    }


    private function getFriends($userId): bool|array
    {
        return $this->db->selectRaw("
            SELECT u.firstname, u.lastname, u.photo
            FROM friends f
            JOIN users u ON f.friend_id = u.id
            WHERE f.user_id = :user_id AND f.status = 'approved'
        ", ['user_id' => $userId]);
    }

    private function getUserPosts($userId): bool|array
    {
        return $this->db->select('posts', 'content, photo', ['user_id' => $userId]);
    }
    public function view($username): void
    {
        // Якщо це профіль поточного користувача, то використовуємо логін із сесії
        if ($username === 'me') {
            $currentUser = $this->session->get('user');
            if (!$currentUser) {
                echo "❌ Користувач не авторизований!";
                return;
            }
            $username = $currentUser['login']; // Використовуємо логін з сесії для профілю
        }

        // Отримуємо дані користувача за логіном
        $userData = $this->getUserDataByLogin($username);

        if (!$userData) {
            echo "❌ Користувач не знайдений!";
            return;
        }

        // Перевірка, чи це профіль поточного користувача
        $currentUser = $this->session->get('user');
        $isOwnProfile = $currentUser && $currentUser['login'] === $userData['login'];

        // Отримуємо друзів та пости користувача
        $friends = $this->getFriends($userData['id']);
        $posts = $this->getUserPosts($userData['id']);

        // Підключаємо view для відображення профілю
        include __DIR__ . '/../views/profile/profile.php';
    }
}
