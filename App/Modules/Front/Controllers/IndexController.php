<?php

namespace App\Modules\Front\Controllers;

use App\Extensions\Front;
use App\Libraries\Email;
use Ice\Validation;

/**
 * Front home controller.
 *
 * @category Controller
 * @package  App
 * @author   Ice <info@iceframework.org>
 * @license  iceframework.org Ice
 * @link     iceframework.org
 */
class IndexController extends Front
{

    /**
     * Display home page
     *
     * @return void
     */
    public function indexAction()
    {
        $this->tag->setTitle(_t('home'));
        $this->app->description = _t('home');
    }

    /**
     * Contact form.
     *
     * @return void
     * @throws Error
     */
    public function contactAction()
    {
        $this->tag->setTitle(_t('contact'));
        $this->app->description = _t('contact');

        if ($this->request->isPost()) {
            $validation = new Validation();

            $validation->rules([
                'fullName' => 'required',
                'email' => 'required|email',
                'repeatEmail' => 'same:email',
                'content' => 'required|length:10,5000',
            ]);

            $validation->validate($this->request->getPost()->getData());

            if (!$validation->valid()) {
                $this->view->setVar('errors', $validation->getMessages());
                $this->flash->warning(_t('flash/warning/errors'));
            } else {
                // Prepare an email
                $email = new Email();
                $email->prepare(_t('contact'), $this->config->app->admin, 'email/contact', [
                    'fullName' => $this->request->getPost('fullName'),
                    'email' => $this->request->getPost('email'),
                    'content' => $this->request->getPost('content'),
                ]);
                $email->addReplyTo($this->request->getPost('email'));

                // Try to send email
                if ($email->Send() === true) {
                    $this->flash->success(_t('flash/success/contact'));
                    unset($_POST);
                } else {
                    throw new Error($email->ErrorInfo);
                }
            }
        }
    }

    /**
     * Set a language.
     *
     * @return void
     */
    public function langAction()
    {
        if ($lang = $this->dispatcher->getParam('id')) {
            // Store lang in session and cookie
            $this->session->set('lang', $lang);
            $this->cookies->set('lang', $lang, time() + 365 * 86400);
        }
        // Go to the last place
        $referer = $this->request->getServer("HTTP_REFERER");
        if (strpos($referer, $this->request->getServer("HTTP_HOST") . "/") !== false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            $this->response->redirect();
        }
    }
}
