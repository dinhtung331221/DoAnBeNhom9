<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;

class AuthController
{
    public function loginForm()
    {
        if (Auth::check()) {
            header('Location: ' . url());
            exit;
        }
        View::render('auth/login', array('title' => 'Đăng nhập'), 'layouts/auth');
    }

    public function login(Request $request)
    {
        if (!$request->validateCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ. Vui lòng thử lại.');
            $this->redirect('login');
        }
        $username = trim((string) $request->input('username', ''));
        $password = (string) $request->input('password', '');
        if (!Auth::attempt($username, $password)) {
            flash('error', 'Tên đăng nhập hoặc mật khẩu không đúng.');
            $this->redirect('login');
        }
        $this->redirect('');
    }

    public function registerForm()
    {
        View::render('auth/register', array('title' => 'Tạo tài khoản'), 'layouts/auth');
    }

    public function register(Request $request)
    {
        if (!$request->validateCsrf()) {
            flash('error', 'Phiên làm việc không hợp lệ.');
            $this->redirect('register');
        }
        $username = trim((string) $request->input('username', ''));
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');
        $confirmation = (string) $request->input('password_confirmation', '');

        if (!preg_match('/^[a-zA-Z0-9_.]{4,50}$/', $username) || mb_strlen($name, 'UTF-8') < 2) {
            flash('error', 'Vui lòng nhập đầy đủ họ tên và tên đăng nhập hợp lệ.');
            $this->redirect('register');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Email không hợp lệ.');
            $this->redirect('register');
        }
        if (strlen($password) < 8 || $password !== $confirmation) {
            flash('error', 'Mật khẩu phải có ít nhất 8 ký tự và khớp phần xác nhận.');
            $this->redirect('register');
        }

        try {
            $statement = Database::connection()->prepare(
                "INSERT INTO users (username, password_hash, ho_ten, email, role)
                 VALUES (:username, :password_hash, :ho_ten, :email, 'customer')"
            );
            $statement->execute(array(
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'ho_ten' => $name,
                'email' => $email !== '' ? $email : null,
            ));
        } catch (\PDOException $exception) {
            flash('error', 'Tên đăng nhập hoặc email đã được sử dụng.');
            $this->redirect('register');
        }
        flash('success', 'Tạo tài khoản thành công. Bạn có thể đăng nhập.');
        $this->redirect('login');
    }

    public function logout(Request $request)
    {
        if ($request->validateCsrf()) {
            Auth::logout();
        }
        $this->redirect('login');
    }

    private function redirect($path)
    {
        header('Location: ' . url($path));
        exit;
    }
}

