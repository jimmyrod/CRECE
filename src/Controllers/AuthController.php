<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Support\Auth;
use App\Support\Response;
use App\Support\Session;

class AuthController
{
    public function __construct(private readonly UserRepository $users)
    {
    }

    public function showLogin(): void
    {
        Response::view('auth/login', [
            'title' => 'Iniciar sesión',
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($email, $password)) {
            Session::flash('success', 'Bienvenido nuevamente.');
            Response::redirect('/');
            return;
        }

        Session::flash('error', 'Credenciales inválidas o cuenta pendiente de aprobación.');
        Response::redirect('/login');
    }

    public function showRegister(): void
    {
        Response::view('auth/register', [
            'title' => 'Crear cuenta',
        ]);
    }

    public function register(): void
    {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'institution' => trim($_POST['institution'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'orcid' => trim($_POST['orcid'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
        ];

        foreach (['institution', 'country', 'orcid', 'phone_number'] as $optionalField) {
            if ($data[$optionalField] === '') {
                $data[$optionalField] = null;
            }
        }

        if (!$data['first_name'] || !$data['last_name'] || !$data['email'] || !$data['password']) {
            Session::flash('error', 'Por favor completa todos los campos obligatorios.');
            Response::redirect('/register');
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'El correo electrónico no es válido.');
            Response::redirect('/register');
            return;
        }

        if (strlen($data['password']) < 8) {
            Session::flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            Response::redirect('/register');
            return;
        }

        if ($this->users->findByEmail($data['email'])) {
            Session::flash('error', 'Ya existe una cuenta con este correo.');
            Response::redirect('/register');
            return;
        }

        $this->users->create($data);
        Session::flash('success', 'Registro recibido. Te notificaremos cuando sea aprobado por el equipo.');
        Response::redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Sesión cerrada correctamente.');
        Response::redirect('/login');
    }
}
