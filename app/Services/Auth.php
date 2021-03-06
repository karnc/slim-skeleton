<?php

namespace App\Services;

use App\Models\User;

class Auth
{
	public function user()
	{
		return User::find(isset($_SESSION['user']) ? $_SESSION['user'] : 0);
	}

	public function check()
	{
		return isset($_SESSION['user']);
	}

	public function attempt($email, $password)
	{
		$user = User::where('email', $email)->first();

		if (! $user) {
			return false;
		}

		if (password_verify($password, $user->password)) {
			$_SESSION['user'] = $user->id;
			return true;
		}

		return false;
	}

	public function logout()
	{
		unset($_SESSION['user']);
	}
}