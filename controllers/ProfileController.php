<?php

namespace controllers;

use core\Controller;
use core\DB;
use core\Session;


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

    private function getUserDataByLogin(string $username)
    {
        return $this->db->selectOne('users', ['login' => $username]) ?: null;
    }

    private function getFriends(int $userId): array
    {
        return $this->db->selectRaw("SELECT u.firstname, u.lastname, u.photo FROM friends f JOIN users u ON f.friend_id = u.id WHERE f.user_id = :user_id AND f.status = 'approved'", ['user_id' => $userId]) ?: [];
    }

    private function getUserPosts(int $userId): array
    {
        $posts = $this->db->select('posts', 'content, photo', ['user_id' => $userId]) ?: [];

        $uniquePosts = [];
        $seenContent = [];

        foreach ($posts as $post) {
            $hash = md5($post['content'] . ($post['photo'] ?? ''));
            if (!in_array($hash, $seenContent)) {
                $seenContent[] = $hash;
                $uniquePosts[] = $post;
            }
        }

        return $uniquePosts;
    }

    public function view($username): void
    {
        $currentUser = $this->session->get('user');

        if ($username === 'me') {
            if (!$currentUser) {
                header('Location: /users/login');
                exit;
            }
            $username = $currentUser['login'];
        }

        $userData = $this->getUserDataByLogin($username);

        if (!$userData) {
            echo "<p class='alert alert-danger'>❌ Користувач не ау знайдений!</p>";
            return;
        }

        $isOwnProfile = $currentUser && $currentUser['login'] === $userData['login'];
        $friends = $this->getFriends($userData['id']);
        $posts = $this->getUserPosts($userData['id']);

        if (!defined('PROFILE_INCLUDED')) {
            define('PROFILE_INCLUDED', true);
            include __DIR__ . '/../views/profile/profile.php';
        }
    }

    public function validateAndUpdateProfile(array $postData, array $fileData)
    {
        session_start();
        if (!isset($_SESSION['user'])) {
            return ['success' => false, 'message' => 'Користувач не авторизований.'];
        }

        $userId = $_SESSION['user']['id'];
        $firstname = trim($postData['firstname'] ?? '');
        $lastname = trim($postData['lastname'] ?? '');
        $bio = trim($postData['bio'] ?? '');
        $address = trim($postData['address'] ?? '');
        $phone = trim($postData['phone'] ?? '');
        $city = trim($postData['city'] ?? '');
        $country = trim($postData['country'] ?? '');
        $errors = [];

        // Валідація
        if (!$firstname || !$lastname) {
            $errors[] = 'Ім\'я та прізвище обов\'язкові!';
        }
        if ($phone && !preg_match('/^\+?[0-9]{7,15}$/', $phone)) {
            $errors[] = 'Некоректний номер телефону.';
        }

        // Завантаження фото
        $photoPath = null;
        if (!empty($fileData['photo']['name'])) {
            $uploadDir = __DIR__ . '/../../uploads/';
            $filename = 'user_' . $userId . '_' . time() . '.' . pathinfo($fileData['photo']['name'], PATHINFO_EXTENSION);
            $photoPath = '/uploads/' . $filename;

            if (!move_uploaded_file($fileData['photo']['tmp_name'], $uploadDir . $filename)) {
                $errors[] = 'Помилка завантаження фото.';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Оновлення БД
        $query = "UPDATE users SET firstname=?, lastname=?, bio=?, address=?, phone=?, city=?, country=?";
        $params = [$firstname, $lastname, $bio, $address, $phone, $city, $country];

        if ($photoPath) {
            $query .= ", photo=?";
            $params[] = $photoPath;
        }

        $query .= " WHERE id=?";
        $params[] = $userId;

        $stmt = $this->db->pdo->prepare($query);
        $updated = $stmt->execute($params);

        if ($updated) {
            $_SESSION['user'] = array_merge($_SESSION['user'], [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'bio' => $bio,
                'address' => $address,
                'phone' => $phone,
                'city' => $city,
                'country' => $country
            ]);

            if ($photoPath) {
                $_SESSION['user']['photo'] = $photoPath;
            }

            return ['success' => true, 'message' => 'Профіль оновлено.'];
        }

        return ['success' => false, 'message' => 'Помилка оновлення профілю.'];
    }
}