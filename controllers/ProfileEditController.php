<?php

namespace controllers;

use core\Session;
use core\DB;

class ProfileEditController
{
    private DB $db;

    public function __construct()
    {
        $this->db = new DB('localhost', 'Layttle', 'Dubrov', '2004Dubrov');
    }

    public function validateAndUpdateProfile($data, $files)
    {
        $errors = [];
        $logFile = 'D:\\wamp64\\domains\\Layttle\\error_cms.txt';

        if (empty($data['firstname'])) {
            $errors[] = "First name is required.";
        }

        if (empty($data['lastname'])) {
            $errors[] = "Last name is required.";
        }

        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }

        if (empty($data['phone']) || !preg_match('/^\+?\d{10,15}$/', $data['phone'])) {
            $errors[] = "Valid phone number is required.";
        }

        if (empty($data['address'])) {
            $errors[] = "Address is required.";
        }

        if (!empty($errors)) {
            file_put_contents($logFile, "Validation Errors:\n" . implode("\n", $errors) . "\n", FILE_APPEND);
            return ['success' => false, 'errors' => $errors];
        }

        $profilePhotoPath = null;
        $coverPhotoPath = null;

        if (!empty($files['profile_photo']['name'])) {
            $uploadResult = $this->processUploadedPhoto($files['profile_photo'], 'profile_photo');
            if ($uploadResult['success']) {
                $profilePhotoPath = $uploadResult['path'];
            } else {
                file_put_contents($logFile, "Profile Photo Upload Error:\n" . implode("\n", $uploadResult['errors']) . "\n", FILE_APPEND);
                return ['success' => false, 'errors' => $uploadResult['errors']];
            }
        }

        if (!empty($files['cover_photo']['name'])) {
            $uploadResult = $this->processUploadedPhoto($files['cover_photo'], 'cover_photo');
            if ($uploadResult['success']) {
                $coverPhotoPath = $uploadResult['path'];
            } else {
                file_put_contents($logFile, "Cover Photo Upload Error:\n" . implode("\n", $uploadResult['errors']) . "\n", FILE_APPEND);
                return ['success' => false, 'errors' => $uploadResult['errors']];
            }
        }

        $updateData = [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'bio' => $data['bio'] ?? null,
        ];

        if ($profilePhotoPath) {
            $updateData['profile_photo'] = $profilePhotoPath;
        }

        if ($coverPhotoPath) {
            $updateData['cover_photo'] = $coverPhotoPath;
        }

        $userId = (new Session)->get('user_id');
        $where = ['id' => $userId];

        $sql = $this->db->generateUpdateQuery('users', $updateData, $where);
        file_put_contents($logFile, "SQL Query:\n" . $sql . "\n", FILE_APPEND);

        $updated = $this->db->update('users', $updateData, $where);

        if (!$updated) {
            file_put_contents($logFile, "Update failed.\n", FILE_APPEND);
            return ['success' => false, 'errors' => ['Failed to update the profile.']];
        }

        file_put_contents($logFile, "Update successful.\n", FILE_APPEND);
        return ['success' => true];
    }

    private function processUploadedPhoto($file, $type)
    {
        $uploadDir = 'uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $errors = [];

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Invalid file type for $type. Allowed types: JPEG, PNG, GIF.";
        }

        if ($file['size'] > 2 * 1024 * 1024) { // 2 MB
            $errors[] = "$type exceeds the maximum file size of 2 MB.";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $filePath = $uploadDir . uniqid($type . '_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'path' => $filePath];
        } else {
            return ['success' => false, 'errors' => ["Failed to upload $type."]];
        }
    }
}
