<?php

namespace controllers;
use core\Controller;
use core\DB;
use core\Session;
use Exception;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ChatController extends Controller
{
    private DB $db;
    private Session $session;

    public function __construct()
    {
        $this->db = new DB('localhost', 'layttle', 'Dubrov', '2004Dubrov');
        $this->session = new Session();
    }
    public function index(): void
    {
        $user = $this->session->get('user');
        if (!$user || !isset($user['id'])) {
            header('Location: /users/login');
            exit;
        }

        $search = $_GET['search'] ?? '';
        $searchQuery = '';
        $params = ['current_user_id' => $user['id']];

        if ($search) {
            $searchQuery = " AND (firstname LIKE :search OR lastname LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $users = $this->db->selectRaw(
            "SELECT id, firstname, lastname FROM users WHERE id != :current_user_id $searchQuery",
            $params
        );

        error_log("Fetched users: " . print_r($users, true), 3, 'D:/wamp64/domains/Layttle/error_cms.txt');

        include __DIR__ . '/../views/chat/index.php';
    }

    public function show(int $receiverId): void
    {
        $user = $this->session->get('user');
        if (!$user || !isset($user['id'])) {
            header('Location: /users/login');
            exit;
        }

        $receiver = $this->db->selectOne('users', ['id' => $receiverId]);
        if (!$receiver) {
            error_log("User not found with ID: " . $receiverId, 3, 'D:/wamp64/domains/Layttle/error_cms.txt');
            throw new Exception('Користувач не знайдений');
        }

        $messages = $this->db->selectRaw(
            "SELECT * FROM messages WHERE 
        (sender_id = :sender_id AND receiver_id = :receiver_id) 
        OR (sender_id = :receiver_id AND receiver_id = :sender_id) 
        ORDER BY created_at ASC",
            [
                'sender_id' => $user['id'],
                'receiver_id' => $receiverId
            ]
        );

        include __DIR__ . '/../views/chat/show.php';
    }

    public function messages(int $receiverId): void
    {
        $user = $this->session->get('user');
        if (!$user || !isset($user['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Неавторизовано']);
            return;
        }

        $messages = $this->db->selectRaw(
            "SELECT * FROM messages WHERE 
            (sender_id = :sender_id AND receiver_id = :receiver_id) 
            OR (sender_id = :receiver_id AND receiver_id = :sender_id) 
            ORDER BY created_at",
            [
                'sender_id' => $user['id'],
                'receiver_id' => $receiverId
            ]
        );

        echo json_encode(['messages' => $messages]);
        exit;
    }

    public function store(int $receiverId): void
    {
        $user = $this->session->get('user');
        if (!$user || !isset($user['id'])) {
            header('Location: /users/login');
            exit;
        }

        $message = $_POST['message'] ?? '';
        $file = $_FILES['file'] ?? null;

        if (empty($message) && !$file) {
            header("Location: /chat/{$receiverId}");
            exit;
        }

        $filePath = null;
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $filePath = '/uploads/messages/' . basename($file['name']);
            move_uploaded_file($file['tmp_name'], __DIR__ . '/../../public' . $filePath);
        }

        $this->db->insert('messages', [
            'sender_id' => $user['id'],
            'receiver_id' => $receiverId,
            'message' => $message,
            'file' => $filePath,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        header("Location: /chat/{$receiverId}");
        exit;
    }
}
