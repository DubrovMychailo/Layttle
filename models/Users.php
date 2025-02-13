<?php


namespace models;
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Core.php';

use core\Model;
use core\Core;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * @property int $id Id
 * @property string $login Логін
 * @property string $password Пароль
 * @property string $firstname Ім'я
 * @property string $lastname Прізвище
 * @property string $role Роль
 */
class Users extends Model
{
    public static $tableName = 'users';

    public static function VerifyLoginAndPassword($login, $password)
    {
        $rows = self::findByCondition(['login' => $login, 'password' => $password]);
        if (!empty($rows))
            return $rows[0];
        else
            return null;
    }

    public static function FindByLogin($login)
    {
        $rows = self::findByCondition(['login' => $login]);
        if (!empty($rows))
            return $rows[0];
        else
            return null;
    }

    public static function IsUserLogged()
    {
        $route = '/Layttle';
        return !empty(Core::get($route)->session->get('user'));
    }

    public static function LoginUser($user)
    {
        $route = '/Layttle';
        Core::get($route)->session->set('user', $user);
    }

    public static function LogoutUser()
    {
        $route = '/Layttle';
        Core::get($route)->session->remove('user');
    }

    public static function RegisterUser($login, $password, $lastname, $firstname)
    {
        $user = new Users();
        $user->login = $login;
        $user->password = $password;
        $user->lastname = $lastname;
        $user->firstname = $firstname;
        $user->save();
    }

    /**
     * Get user by ID
     *
     * @param int $id User ID
     * @return Users|null
     */
    public static function getUserById($id)
    {
        $rows = self::findByCondition(['id' => $id]);
        if (!empty($rows))
            return $rows[0];
        else
            return null;
    }

    /**
     * Get role of the currently logged-in user
     *
     * @return string|null
     */
    public static function getUserRole()
    {
        $route = '/Layttle';
        $user = Core::get($route)->session->get('user');
        return $user['role'] ?? null;
    }
}
