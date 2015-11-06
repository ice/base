<?php

namespace App\Modules\Frontend\Controllers;

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
class UserController extends IndexController
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

        if ($this->request->hasPost('submit_signin') && $this->request->hasPost('username') &&
            $this->request->hasPost('password')) {
            $login = $this->auth->login(
                $this->request->getPost('username'),
                $this->request->getPost('password'),
                $this->request->getPost('rememberMe') ? true : false
            );

            if (!$login) {
                $errors = [];

                if ($login === null) {
                    $errors['username'][] = _t('Field :field is not valid', [':field' => _t('username')]);
                } else {
                    $errors['password'][] = _t('Field :field is not valid', [':field' => _t('password')]);
                }

                $this->view->setVar('errors', new Arr($errors));
                $this->flash->warning(_t('flash/warning/errors'));
            } else {
                $referer = $this->request->getHTTPReferer();
                $host = parse_url($referer, PHP_URL_HOST) . (parse_url($referer, PHP_URL_PORT));

                if (parse_url($referer, PHP_URL_PORT)) {
                    $host .= parse_url($referer, PHP_URL_PORT);
                }

                $back = !empty($referer) &&
                    strpos(parse_url($referer, PHP_URL_PATH), '/user/signin') !== 0 &&
                    strpos(parse_url($referer, PHP_URL_PATH), '/user/signup') !== 0 &&
                    strpos(parse_url($referer, PHP_URL_PATH), '/user/activation') !== 0 &&
                    $host == $this->request->getServer("HTTP_HOST");

                if ($back) {
                    return $this->response->setHeader("Location", $referer);
                } else {
                    return $this->dispatcher->forward(['handler' => 'index', 'action' => 'index']);
                }
            }
        }
    }

    /**
     * Sign in the user through social network
     */
    public function signinbyAction()
    {
        if (!$this->auth ->loggedIn()) {
            $this->tag->setTitle(_t('signIn'));
            $this->app->description = _t('signIn');

            $params = $this->router->getParams();
            if (isset($params['param'])) {
                $by = $params['param'];
                $login = false;

                switch ($by) {
                    case 'facebook':
                        $social = new Social(new Social\Facebook());

                        if (!$this->request->hasGet($social->getResponseType())) {
                            $this->view->setContent(false);
                            return $this->response->redirect($social->getAuthUrl(), 302, true);
                        } else {
                            // Check if access token already exist in the session
                            if ($this->session->has('access_token')) {
                                $social->setAccessToken($this->session->get('access_token'));
                            }

                            if ($social->authenticate()) {
                                // Store the access token in the session, it can be retrieved only once
                                $this->session->set('access_token', $social->getAccessToken());
                                
                                // Try to login by social
                                $login = $this->auth->loginBy($social);
                            } else {
                                parent::noAccess();
                            }
                        }
                        break;
                    case 'google':
                        $social = new Social(new Social\Google());

                        if (!$this->request->hasGet($social->getResponseType())) {
                            $this->view->setContent(false);
                            return $this->response->redirect($social->getAuthUrl(), 302, true);
                        } else {
                            // Check if access token already exist in the session
                            if ($this->session->has('access_token')) {
                                $social->setAccessToken($this->session->get('access_token'));
                            }

                            if ($social->authenticate()) {
                                // Store the access token in the session, it can be retrieved only once
                                $this->session->set('access_token', $social->getAccessToken());
                                
                                // Try to login by social
                                $login = $this->auth->loginBy($social);
                            } else {
                                parent::noAccess();
                            }
                        }
                        break;
                    case 'twitter':
                        $social = new Social(new Social\Twitter());

                        if (!$this->request->hasGet($social->getResponseType())) {
                            $this->view->setContent(false);
                            return $this->response->redirect($social->getAuthUrl(), 302, true);
                        } else {
                            if ($social->authenticate()) {
                                // Try to login by social
                                $login = $this->auth->loginBy($social);
                            } else {
                                parent::noAccess();
                            }
                        }
                        break;
                    default:
                        parent::notFound();
                        break;
                }

                if ($login === null) {
                    $this->view->setFile('user/signupby');
                    $this->view->setVar('email', $social->getEmail());

                    // Add social auth for existing account
                    if ($user = Users::findOne(['email' => $social->getEmail()])) {
                        // Update user's data from social network
                        $user->socialUpdate($social);

                        // Redirect to this action and sign in the user
                        $this->response->redirect('user/signinby/' . $social->getProvider());
                    }

                    // Sign up new user by social network
                    if ($this->request->isPost() == true) {
                        // Try to signup new user
                        $user = new Users();
                        $signup = $user->signupby($social);

                        if ($signup instanceof Users) {
                            // Remove access token from the session
                            $this->session->remove('access_token');
                            
                            // Redirect to this action and sign in the user
                            $this->response->redirect('user/signinby/' . $social->getProvider());
                        } else {
                            $this->view->setVar('errors', $signup);
                            $this->flash->warning(_t('flash/warning/errors'));
                        }
                    }
                } elseif ($login == false) {
                    parent::noAccess();
                } else {
                    // Sign in the user by social network
                    // Remove access token from the session
                    $this->session->remove('access_token');

                    // Back to last place
                    $referer = $this->request->getHTTPReferer();
                    $host = parse_url($referer, PHP_URL_HOST);

                    if (parse_url($referer, PHP_URL_PORT)) {
                        $host .= parse_url($referer, PHP_URL_PORT);
                    }

                    $back = !empty($referer) &&
                        strpos(parse_url($referer, PHP_URL_PATH), '/user/signin') !== 0 &&
                        strpos(parse_url($referer, PHP_URL_PATH), '/user/signinby') !== 0 &&
                        strpos(parse_url($referer, PHP_URL_PATH), '/user/signup') !== 0 &&
                        $host == $this->request->getServer("HTTP_HOST");

                    if ($back) {
                        return $this->response->setHeader("Location", $referer);
                    } else {
                        $this->response->redirect();
                    }
                }
            } else {
                parent::notFound();
            }
        } else {
            $this->response->redirect();
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
