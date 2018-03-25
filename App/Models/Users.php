<?php

namespace App\Models;

use App\Error;
use Ice\Auth\Driver\Model\Roles;
use Ice\Auth\Driver\Model\Roles\Users as AuthRolesUsers;
use Ice\Auth\Driver\Model\Users\Social as UserSocial;
use Ice\Auth\Driver\Model\Users as AuthUsers;
use Ice\Validation;

/**
 * Users model.
 *
 * @category Models
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class Users extends AuthUsers
{

    /**
     * This fields are valid and only them will be saved
     */
    protected $fields = [
        'id',
        'email',
        'username',
        'password',
        'logins',
        'lastlogin'
    ];

    /**
     * Rules to validate user during create
     */
    protected $rules = [
        'username' => 'required|length:4,24|regex:/[a-z][a-z0-9_-]{3,}/i|notIn:admin,index,user,root|unique:users',
        'password' => 'required|length:5,32',
        'email' => 'required|email|unique:users',
    ];

    /**
     * Add user relations
     *
     * <pre><code>
     *  $this->hasMany('id', __NAMESPACE__ . '\Posts', 'user_id', [
     *      'alias' => 'Posts'
     *  ]);
     * </code></pre>
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * Add role to user
     */
    public function addRole($role = 'login')
    {
        if ($this->getRole($role)) {
            // This user has this role
            return null;
        } else {
            // Add login role
            $roleUser = new AuthRolesUsers();
            $roleUser->user_id = $this->getId();
            $roleUser->role_id = Roles::findOne(['name' => $role])->getId();

            if ($roleUser->create() === true) {
                return true;
            } else {
                throw new Error($roleUser->getError());
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
        $userSocial->user_id = $this->getId();

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
