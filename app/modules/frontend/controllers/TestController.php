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
        $errors = $user->getMessages();
        $activation = $user->activation();

        $this->view->setContent($this->dump->vars($create, $activation, $errors));
    }

    /**
     * Sign in the user:user
     */
    public function signinAction()
    {
        $login = $this->auth->login('user', 'user', true);

        echo $this->dump->vars($login);
    }

    public function newAction()
    {
        $user = new \Ice\Auth\Driver\Model\Users(["username" => 'user']);

        echo $this->dump->vars($user);
    }

    public function findAction()
    {
        //$user = \Ice\Auth\Driver\Model\Users::findOne(["username" => 'user']);
        $user = Users::findOne('1');

        echo $this->dump->vars($user);
    }

    public function loadAction()
    {
        $user = new \Ice\Auth\Driver\Model\Users();

        echo $this->dump->vars($user->loadOne(["username" => 'user']));
    }
}
