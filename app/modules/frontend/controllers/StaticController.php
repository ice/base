<?php

namespace App\Modules\Frontend\Controllers;

use App\Error;
use App\Extensions\Frontend;
use App\Libraries\Email;
use Ice\Arr;
use Ice\Validation;

/**
 * Frontend static Controller
 *
 * @package     Ice/Base
 * @category    Controller
 */
class StaticController extends Frontend
{

    /**
     * Display contact form
     */
    public function getContactAction()
    {
        $this->tag->setTitle(_t('contact'));
        $this->app->description = _t('contact');

        $this->app->layout->replace([
            'left' => 'mdl-cell--3-col',
            'content' => 'mdl-cell--6-col mdl-cell--8-col-tablet'
        ]);
    }

    /**
     * Validate _POST and send email
     *
     * @throws Error
     */
    public function postContactAction()
    {
        $this->getContactAction();
        //$this->app->request(['action' => 'getContact']);

        $validation = new Validation();

        $validation->rules([
            'fullName' => 'required',
            'email' => 'required|email',
            'repeatEmail' => 'same:email',
            'content' => 'required|length:10,5000',
        ]);

        $valid = $validation->validate($_POST);

        if (!$valid) {
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
