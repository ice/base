<?php

namespace App\Modules\Frontend\Controllers;

use App\Extensions\Frontend;
use App\Models\Users;
use App\Services\UserService;
use Ice\Validation;
use Ice\Validation\Validator\Email;
use Ice\Validation\Validator\Required;
use Ice\Validation\Validator\Same;

/**
 * Frontend Test Controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class TestController extends Frontend
{

    /**
     * Sign up the user:user /base/test/demo
     * Sign up the admin:admin /base/test/demo/admin
     */
    public function demoAction()
    {
        $username = $this->dispatcher->getParam('param', null, 'user');
        $user = new UserService();

        // Skip rules
        $user->setRules([], false);

        $create = $user->signup([
            'username' => $username,
            'password' => $username,
            'repeatPassword' => $username,
            'email' => $username . '@example.com',
            'repeatEmail' => $username . '@example.com',
        ]);
        $activation = $user->addRole();

        $this->view->setContent($this->dump->vars($create, $activation));
    }

    public function adminAction()
    {
        $user = Users::findOne(["username" => 'admin']);
        $admin = $user->addRole('admin');

        $this->view->setContent($this->dump->vars($admin));
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


    public function validationAction()
    {
        $data = [
            'emailAddress' => '',
            'repeatEmailAddress' => 'user@example.com',
        ];

        $validation = new Validation();
        $validation->setHumanLabels(true);

        // $validation->rule('emailAddress', new Required());
        // $validation->rule('emailAddress', new Email());
        // $validation->rule('repeatEmailAddress', new Same(['other' => 'emailAddress']));

        // $validation->rules([
        //     'emailAddress' => [
        //         new Required(),
        //         new Email()
        //     ],
        //     'repeatEmailAddress' => new Same(['other' => 'emailAddress'])
        // ]);

        $validation->rules([
            'emailAddress' => [
                'required',
                'email'
            ],
            'repeatEmailAddress' => [
                'same' => [
                    'other' => 'emailAddress',
                    'message' => ':field must be the same as :other',
                    'label' => 'Repeat E-mail',
                    'labelOther' => 'E-mail'
                ]
            ]
        ]);

        // $validation->rules([
        //     'emailAddress' => 'required|email',
        //     'repeatEmailAddress' => 'same:emailAddress'
        // ]);

        $validation->validate($data);

        if (!$validation->valid()) {
            $messages = $validation->getMessages();
        }

        var_dump($messages->all());

        // $data = [
        //     'username' => 'ice123_framework'
        // ];

        // $validation = new Validation();
        // $validation->setFilters([
        //     'username' => 'alpha'
        // ]);

        // $validation->validate($data);

        // var_dump($validation->getValue('username'));

        $this->app->setAutoRender(false);
    }
}
