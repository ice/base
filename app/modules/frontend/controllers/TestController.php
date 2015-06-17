<?php

namespace App\Modules\Frontend\Controllers;

use App\Models\Users;

/**
 * Frontend Test Controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class TestController extends IndexController
{

    /**
     * Sign up the user:user
     */
    public function signupAction()
    {
        $user = new Users();
        $create = $user->create([
            'email' => 'user@example.com',
            'username' => 'user',
            'password' => $this->auth->hash('user'),
        ]);

        echo $this->dump->vars($create);
    }

    /**
     * Sign in the user:user
     */
    public function signinAction()
    {
        $login = $this->auth->login('user', 'user', true);

        echo $this->dump->vars($login);
    }
}
