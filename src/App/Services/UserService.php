<?php

namespace App\Services;

use App\Error;
use App\Libraries\Email;
use App\Models\Users;
use Ice\Auth\Driver\Model\Users\Social as UserSocial;
use Ice\Auth\Social;
use Ice\Mvc\Service;
use Ice\Validation;

/**
 * User service.
 *
 * @category Services
 * @package  Base
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class UserService extends Service
{

    public $social = null;

    /**
     * Set the model
     *
     * @param object $model User
     */
    public function __construct(Users $model)
    {
        $this->setModel($model);
    }

    /**
     * Get the social
     *
     * @return object Social
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * Sign in by social network
     *
     * @return mixed
     */
    public function signin()
    {
        return $this->auth->login(
            $this->request->getPost('username'),
            $this->request->getPost('password'),
            $this->request->getPost('rememberMe') ? true : false
        );
    }

    /**
     * Sign in by social network
     *
     * @param string $provider Provider nme
     *
     * @return mixed
     */
    public function signinby($provider)
    {
        $login = null;

        switch ($provider) {
            case 'facebook':
                $this->social = new Social(new Social\Facebook());
                break;
            case 'google':
                $this->social = new Social(new Social\Google());
                break;
            case 'twitter':
                $this->social = new Social(new Social\Twitter());
                break;
            default:
                return false;
        }

        if (!$this->request->hasGet($this->social->getResponseType())) {
            return $this->response->redirect($this->social->getAuthUrl(), 302, true);
        } else {
            // Check if access token already exist in the session
            if ($this->session->has('access_token')) {
                $this->social->setAccessToken($this->session->get('access_token'));
            }
            if ($this->social->authenticate()) {
                // Store the access token in the session, it can be retrieved only once
                $this->session->set('access_token', $this->social->getAccessToken());
                // Try to login by social
                $login = $this->auth->loginBy($this->social);
            } else {
                return false;
            }
        }

        return $login;
    }

    /**
     * Sign up new user
     *
     * @param array $data Data used to create
     *
     * @return mixed
     */
    public function signup($data = null)
    {
        $auth = $this->auth;

        if (!is_array($data)) {
            // Get _POST data
            $data = $this->request->getPost()->getData();
        }

        // Hash password after validate and before save
        $this->di->hook('model.after.validate', function () use ($auth) {
            $this->set('password', $auth->hash($this->get('password')));
        });

        // Add extra validation for fields that won't be save but must pass
        $extra = new Validation($data);
        $extra->rules([
            'repeatPassword' => 'same:password',
            'repeatEmail' => 'same:email',
        ]);

        // Only valid fields are accepted from the $data
        if ($this->create($data, $extra) === true) {
            // If user was created, send activation email
            $hash = md5($this->getId() . $this->get('email') . $this->get('password') . $this->config->auth->hash_key);

            $email = new Email();
            $email->prepare(
                _t('activation'),
                $this->get('email'),
                'email/activation',
                ['username' => $this->get('username'), 'id' => $this->getId(), 'hash' => $hash]
            );

            if ($email->send() === true) {
                unset($_POST);
                // Return the user
                return $this->getModel();
            } else {
                throw new Error($this->getError());
            }
        } else {
            return $this->getMessages();
        }
    }

    /**
     * Sign up by social network
     *
     * @param object $social Adapter
     * @param array  $data   Data used to create
     *
     * @return mixed
     */
    public function signupby($social, $data = null)
    {
        // Get email from social and set to _POST
        if (!is_array($data)) {
            // Set _POST[email] if exist in the social data
            if ($email = $social->getEmail()) {
                $this->request->getPost()->set('email', $email);
            }
            // Get _POST data
            $data = $this->request->getPost()->getData();
        }

        // Password for social is unnecessary
        $this->setRules($this->getRules(['username', 'email']), false);

        // Only valid fields are accepted from the $data
        if ($this->create($data) === true) {
            // If user was created, add social auth
            $userSocial = new UserSocial();
            $userSocial->social_id = $social->getSocialId();
            $userSocial->type = $social->getProvider();
            $userSocial->user_id = $this->getId();

            // Try to create social auth
            if ($userSocial->create() === true) {
                // Add login role
                if ($this->addRole() === true) {
                    unset($_POST);
                    // Return the user
                    return $this->getModel();
                }
            } else {
                throw new Error($userSocial->getError());
            }
        } else {
            return $this->getMessages();
        }
    }
}
