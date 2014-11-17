<?php

namespace App\Modules\Frontend\Controllers;

use Ice\Arr;
use App\Models\Users;

/**
 * Frontend User Controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class UserController extends IndexController
{

    /**
     * Activate the user
     */
    public function activationAction()
    {
        $this->tag->setTitle(__('Activation'));
        $this->view->setVar('title', __('Activation'));

        $params = $this->router->getParams();

        if (isset($params['id']) && isset($params['param'])) {
            $id = $params['id'];
            $hash = $params['param'];
            $user = Users::findOne($id);

            if ($user && md5($user->id . $user->email . $user->password . $this->config->auth->hash_key) == $hash) {
                $activation = $user->activation();

                if ($activation === null) {
                    $this->flash->info(
                        '<strong>' . __('Information') . '!</strong> ' .
                        __("Activation has already been completed.")
                    );
                } elseif ($activation === true) {
                    $this->flash->success(
                        '<strong>' . __('Success') . '!</strong> ' .
                        __("Activation completed. Please log in.")
                    );
                    // Redirect to sign in
                    $this->view->setVar('redirect', 'user/signin');
                }
            } else {
                $this->flash->error(
                    '<strong>' . __('Error') . '!</strong> ' .
                    __("Activation cannot be completed. Invalid username or hash.")
                );
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
            
        } else {
            $this->tag->setTitle(__('No access'));
            $this->flash->error('<strong>' . __('Error') . '!</strong> ' . __("Please log in to access."));
            $this->view->setVars([
                'title' => __('No access'),
                'redirect' => 'user/signin',
            ]);
            $this->view->setContent($this->view->partial('message'));
        }
    }

    /**
     * Sign in the user
     */
    public function signinAction()
    {
        $this->tag->setTitle(__('Sign in'));
        $this->siteDesc = __('Sign in');

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
                    $errors['username'] = __('Field :field is not valid', [':field' => __('Username')]);
                } else {
                    $errors['password'] = __('Field :field is not valid', [':field' => __('Password')]);
                }

                $this->view->setVar('errors', new Arr($errors));
                $this->flash->warning('<strong>' . __('Warning') . '!</strong> ' . __('Please correct the errors.'));
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
        $this->tag->setTitle(__('Sign up'));
        $this->siteDesc = __('Sign up');

        if ($this->request->isPost() == true) {
            $user = new Users();
            $signup = $user->signup();

            if ($signup instanceof Users) {
                $this->flash->notice(
                    '<strong>' . __('Success') . '!</strong> ' .
                    __("Check Email to activate your account.")
                );
            } else {
                $this->view->setVar('errors', new Arr($user->getMessages()));
                $this->flash->warning('<strong>' . __('Warning') . '!</strong> ' . __("Please correct the errors."));
            }
        }
    }
}
