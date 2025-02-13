<?php
namespace controllers;

use models\Users;
use core\Controller;
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class UsersController extends Controller
{
    public function actionLogin($params)
    {
        if (Users::IsUserLogged()) {
            return $this->redirect('/');
        }

        if ($this->isPost) {
            $user = Users::VerifyLoginAndPassword($this->post->get('login'), $this->post->get('password'));
            if (!empty($user)) {
                Users::LoginUser($user);
                $_SESSION['user_id'] = $user['id'];
                return $this->redirect('/');
            } else {
                $this->addErrorMessage('Неправильний логін чи пароль');
            }
        }

        // Використання відносного шляху до файлу шаблону
        return $this->render('users/login.php');
    }

    public function actionRegister()
    {
        if ($this->isPost) {
            $postData = [
                'login' => $this->post->get('login') ?? '',
                'password' => $this->post->get('password') ?? '',
                'password2' => $this->post->get('password2') ?? '',
                'lastname' => $this->post->get('lastname') ?? '',
                'firstname' => $this->post->get('firstname') ?? ''
            ];

            $user = Users::FindByLogin($postData['login']);
            if (!empty($user)) {
                $this->addErrorMessage('Користувач із таким логіном вже існує');
            }
            if (strlen($postData['login']) === 0)
                $this->addErrorMessage('Логін не вказано');
            if (strlen($postData['password']) === 0)
                $this->addErrorMessage('Пароль не вказано');
            if ($postData['password'] != $postData['password2'])
                $this->addErrorMessage('Паролі не співпадають');
            if (strlen($postData['lastname']) === 0)
                $this->addErrorMessage('Прізвище не вказано');
            if (strlen($postData['firstname']) === 0)
                $this->addErrorMessage('Ім\'я не вказано');

            if (!$this->isErrorMessageExists()) {
                Users::RegisterUser($postData['login'], $postData['password'], $postData['lastname'], $postData['firstname']);
                return $this->redirect('/users/registersuccess');
            }
        }

        $data = [
            'login' => $this->post->get('login') ?? '',
            'lastname' => $this->post->get('lastname') ?? '',
            'firstname' => $this->post->get('firstname') ?? ''
        ];

        return $this->render('users/register.php', $data);
    }

    public function actionRegistersuccess()
    {
        return $this->render('users/registersuccess.php');
    }

    public function actionLogout()
    {
        Users::LogoutUser();
        return $this->redirect('/users/login');
    }
}
