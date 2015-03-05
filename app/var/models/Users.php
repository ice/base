<?php

namespace App\Models;

use Ice\Auth\Driver\Model\Users as AuthUsers;
use Ice\Auth\Driver\Model\Roles;
use Ice\Auth\Driver\Model\Roles\Users as AuthRolesUsers;
use Ice\Validation;
use App\Error;
use App\Libraries\Email;

class Users extends AuthUsers
{

    /**
     * This fields are valid and only them will be saved
     *
     * Zephir issue #520
     */
    protected $fields = [
        'id',
        'email',
        'username',
        'password',
        'logins',
        'lastlogin'
    ];
    protected $rules = [
        'username' => [
            'required',
            'length' => [
                'min' => 4,
                'max' => 24
            ],
            'regex' => [
                'pattern' => '/[a-z][a-z0-9_-]{3,}/i',
            ],
            'notIn' => ['about', 'admin', 'contact', 'help', 'index', 'info', 'lang', 'user', 'privacy', 'terms'],
            'unique' => [
                'from' => 'users'
            ]
        ],
        'password' => 'required|length:5,32',
        'email' => 'required|email|unique:users',
    ];
    protected $labels = [
        'username' => 'Username',
        'password' => 'Password',
        'email' => 'Email',
    ];

    /**
     * Zephir issue #520
     * Can't overwrite the default array data if extends
     */
    public function onConstruct()
    {
        $this->setFields($this->fields);
        $this->setRules($this->rules);
        $this->setLabels($this->labels);
    }

    /**
     * Add user relations
     *
     * <code>
     *  $this->hasMany('id', __NAMESPACE__ . '\Posts', 'user_id', [
     *      'alias' => 'Posts'
     *  ]);
     * </code>
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * Activation user
     */
    public function activation()
    {
        if ($this->getRole()) {
            // This user has login role, activation has already been completed
            return null;
        } else {
            // Add login role
            $roleUser = new AuthRolesUsers();
            $roleUser->user_id = $this->id;
            $roleUser->role_id = Roles::findOne(['name' => 'login'])->id;

            if ($roleUser->create() === true) {
                return true;
            } else {
                //echo $this->getDi()->dump->vars($roleUser);
                return false;
            }
        }
    }

    /**
     * Sign up new user
     *
     * @return mixed
     */
    public function signup()
    {
        $di = $this->getDi();
        $auth = $di->getAuth();

        // Hash password after validate and before save
        $di->hook('model.after.validate', function ($this) use ($auth) {
            $data = $this->all();

            $this->replace(['password' => $auth->hash($data['password'])]);
        });

        // Add extra validation for fields that won't be save but must pass
        $extra = new Validation($_POST);
        $extra->rules([
            'repeatPassword' => 'same:password',
            'repeatEmail' => 'same:email',
        ]);
        $extra->setLabels([
            'repeatPassword' => 'Repeat password',
            'repeatEmail' => 'Repeat email',
        ]);

        // Only valid _fields are accepted from the _POST
        if ($this->create($_POST, $extra) === true) {
            $hash = md5($this->id . $this->email . $this->password . $this->getDi()->getConfig()->auth->hash_key);
            $email = new Email();
            $email->prepare(
                __('Activation'),
                $this->email,
                'email/activation',
                ['username' => $this->username, 'id' => $this->id, 'hash' => $hash]
            );

            if ($email->Send() === true) {
                unset($_POST);
                return $this;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Sign up by social network
     */
    public function signupby($social)
    {
        $validation = new Validation();

        $validation->rules([
            'username' => $this->rules['username']
        ]);

        if (!$social->getEmail()) {
            $validation->rule('email', $this->rules['email']);
        }
        
        $valid = $validation->validate($_POST);

        if (!$valid) {
            return $validation->getMessages();
        } else {
            $this->username = $this->request->getPost('username');
            $this->email = $social->getEmail() ? $social->getEmail() : $this->request->getPost('email');

            if ($this->create() === true) {
                unset($_POST);

                // Add social auth
                $userSocial = new UserSocial();
                $userSocial->social_id = $social->getSocialId();
                $userSocial->type = $social->getProvider();
                $userSocial->user_id = $this->id;

                if ($userSocial->create() === true) {
                    // Add login role
                    $roleUser = new RolesUsers();
                    $roleUser->user_id = $this->id;
                    $roleUser->role_id = Roles::findOne(['name' => 'login'])->id;

                    if ($roleUser->create() === true) {
                        return $this;
                    }
                }
                return false;
            } else {
                throw new Error($this->getError());
            }
        }
    }

    /**
     * Update existing user, add social loggin
     */
    public function socialUpdate($social)
    {
        // Add social auth
        $userSocial = new UserSocial();
        $userSocial->social_id = $social->getSocialId();
        $userSocial->type = $social->getProvider();
        $userSocial->user_id = $this->id;

        if ($userSocial->create() === true) {
            return true;
        }
        return false;
    }

    /**
     * Change user email
     */
    public function changeEmail()
    {
        // Email must be unique except current email
        $this->setRules([
            'email' => 'required|email|unique:users:,' . $this->get('id'),
        ]);

        // Add extra validation for fields that won't be save but must pass
        $extra = new Validation($_POST);
        $extra->rules([
            'repeatEmail' => 'same:email',
        ]);
        $extra->setLabels([
            'repeatEmail' => 'Repeat email',
        ]);

        if ($this->update($_POST, $extra) === true) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Change user password
     */
    public function changePassword()
    {
        $di = $this->getDi();
        $auth = $di->getAuth();

        // Hash password after validate and before save
        $di->hook('model.after.validate', function ($fields) use ($auth) {
            $fields['password'] = $auth->hash($fields['password']);

            return $fields;
        });

        $data = [
            'currentPassword' => $auth->hash($_POST['currentPassword'])
        ];

        // Add extra validation for fields that won't be save but must pass
        $extra = new Validation($data);
        $extra->rules([
            'currentPassword' => 'required|same:' . $this->get('password'),
        ]);
        $extra->setLabels([
            'currentPassword' => 'Current password',
        ]);

        $this->setLabels([
            'password' => 'New password',
        ]);

        if ($this->update($_POST) === true) {
            return $this;
        } else {
            return false;
        }
    }
}
