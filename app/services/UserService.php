<?php

namespace App\Services;

use App\Libraries\Email;
use App\Models\Users;
use Ice\Arr;
use Ice\Mvc\Service;
use Ice\Validation;

class UserService extends Service
{

    /**
     * Set the model
     */
    public function onConstruct()
    {
        $this->setModel(new Users());
    }

    /**
     * Sign up new user
     *
     * @return mixed
     */
    public function signup($data = [])
    {
        $auth = $this->di->auth;
        $data = $data ? $data : $this->request->getPost()->getData();

        // Hash password after validate and before save
        $this->di->hook('model.after.validate', function ($this) use ($auth) {
            $this->set('password', $auth->hash($this->get('password')));
        });

        
        // Add extra validation for fields that won't be save but must pass
        $extra = new Validation($data);
        $extra->rules([
            'repeatPassword' => 'same:password',
            'repeatEmail' => 'same:email',
        ]);

        // Only valid fields are accepted from the _POST
        if ($this->create($data, $extra) === true) {
            $hash = md5($this->getId() . $this->get('email') . $this->get('password') . $this->config->auth->hash_key);
            
            $email = new Email();
            $email->prepare(
                _t('activation'),
                $this->get('email'),
                'email/activation',
                ['username' => $this->get('username'), 'id' => $this->getId(), 'hash' => $hash]
            );

            if ($email->Send() === true) {
                unset($_POST);
                return $this->getModel();
            } else {
                return false;
            }
        } else {
            return $this->getMessages();
        }
    }
}
