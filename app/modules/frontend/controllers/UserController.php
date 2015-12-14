<?php

namespace App\Modules\Frontend\Controllers;

use App\Extensions\Frontend;
use App\Models\Users;
use App\Services\UserService;
use Ice\Arr;
use Ice\Auth\Social;

/**
 * Frontend User Controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class UserController extends Frontend
{

    public $userService;

    public function onConstruct()
    {
        $this->userService = new UserService();
    }

    /**
     * Activate the user
     */
    public function activationAction()
    {
        $this->tag->setTitle(_t('activation'));
        $this->view->setVar('title', _t('activation'));

        $params = $this->router->getParams();

        if (isset($params['id']) && isset($params['param'])) {
            $id = $params['id'];
            $hash = $params['param'];
            $user = Users::findOne($id);

            if ($user && md5($user->id . $user->email . $user->password . $this->config->auth->hash_key) == $hash) {
                $activation = $user->addRole();

                if ($activation === null) {
                    $this->flash->info(_t('flash/notice/activation'));
                } elseif ($activation === true) {
                    $this->flash->success(_t('flash/success/activation'));
                    // Redirect to sign in
                    $this->view->setVar('redirect', 'user/signin');
                }
            } else {
                $this->flash->error(_t('flash/danger/activation'));
            }

            $this->view->setContent($this->view->partial('message'));
        } else {
            parent::notFound();
        }
    }

    /**
     * Display user profile
     */
    public function indexAction()
    {
        if ($this->auth->loggedIn()) {
            // Logged in
        } else {
            parent::noAccess('user/signin');
        }
    }

    /**
     * Sign in the user
     */
    public function signinAction()
    {
        $this->tag->setTitle(_t('signIn'));
        $this->app->description = _t('signIn');
        $provider = $this->dispatcher->getParam('param');
        $referer = $this->request->getHTTPReferer();

        // Check if referer is this host
        if (strpos(parse_url($referer, PHP_URL_PATH), $this->config->app->base_uri . 'user/signin') !== 0 &&
            parse_url($referer, PHP_URL_HOST) .
            (parse_url($referer, PHP_URL_PORT) ? ':' . parse_url($referer, PHP_URL_PORT) : '') ==
            $this->request->getServer("HTTP_HOST")) {
            $this->session->set('referer', $referer);
        }

        // Detect the login way
        if ($this->request->isPost() && $this->request->hasPost('username') && $this->request->hasPost('password')) {
            // Try to login by username and password
            $login = $this->userService->signin();
        } elseif ($provider) {
            // Try to login by social network
            $login = $this->userService->signinby($provider);

            if ($login instanceof \Ice\Http\Response) {
                // Redirect to social page
                return $login;
            }
        }

        if ($this->request->isPost() || $provider) {
            $errors = [];

            // If the login fails
            if ($provider && $login === null) {
                // The user doesn't exist, load view to select the new username
                $this->view->setFile('user/signupby');

                // Fetch social data
                $social = $this->userService->getSocial();
                $email = $social->getEmail();
                $this->view->setVar('email', $email);

                // Add social auth for existing account
                if ($email && $user = Users::findOne(['email' => $email])) {
                    // Update user's data from social network
                    $user->socialUpdate($social);

                    // Redirect to this action and sign in the user
                    $this->response->redirect('user/signin/' . $provider);
                }

                // Sign up new user by social network
                if ($this->request->isPost() == true) {
                    // Try to signup new user
                    $signup = $this->userService->signupby($social);

                    if ($signup instanceof Users) {
                        // Update user's data from social network
                        $signup->socialUpdate($social);
                        
                        // Redirect to this action and sign in the user
                        $this->response->redirect('user/signin/' . $provider);
                    } else {
                        $errors = $signup;
                    }
                }
            } elseif ($provider && $login === false) {
                $errors['provider'][] = _t('Field :field is not valid', [':field' => $provider]);
            } elseif ($login === null) {
                $errors['username'][] = _t('Field :field is not valid', [':field' => _t('username')]);
            } elseif ($login === false) {
                $errors['password'][] = _t('Field :field is not valid', [':field' => _t('password')]);
            }

            if (!$login) {
                $this->view->setVar('errors', new Arr($errors));
                $this->flash->warning(_t('flash/warning/errors'));
            } else {
                // Sign in the user by social network, remove access token from the session if exist
                $this->session->remove('access_token');

                // Back to last place
                $referer = $this->session->get('referer');
                $this->session->remove('referer');
                $except = [
                    $this->config->app->base_uri . 'user/signup',
                    $this->config->app->base_uri . 'user/activation'
                ];

                if (!empty($referer) && !in_array(parse_url($referer, PHP_URL_PATH), $except)) {
                    return $this->response->setHeader("Location", $referer);
                } else {
                    return $this->dispatcher->forward(['handler' => 'index', 'action' => 'index']);
                }
            }
        }
    }

    /**
     * Sign out the user
     */
    public function signoutAction()
    {
        $this->auth->logout();
        $this->response->redirect();
    }

    /**
     * Sign up the user
     */
    public function signupAction()
    {
        $this->tag->setTitle(_t('signUp'));
        $this->app->description = _t('signUp');

        if ($this->request->isPost() == true) {
            $signup = $this->userService->signup();

            if ($signup instanceof Users) {
                $this->flash->notice(_t('flash/notice/checkEmail'));
            } else {
                $this->view->setVar('errors', new Arr($signup));
                $this->flash->warning(_t('flash/warning/errors'));
            }
        }
    }
}
