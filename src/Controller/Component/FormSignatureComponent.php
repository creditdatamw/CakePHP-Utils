<?php
namespace CreditData\Cake\Controller\Component;

use CreditData\Cake\Exception\DuplicateFormException;

use Cake\Controller\Component;
use Cake\Event\Event;
use Cake\Log\Log;

/**
 * Component prevents double submission of a form by assigning a unique
 * one time signature to the form.
 */
class FormSignatureComponent extends Component
{
    const MAX_SIMULTANEOUS_FORMS = 5;
        // User can't have more than this number of forms open at any point in time.

    const FORM_VALIDITY_PERIOD = 180000000;     // 30 minutes in millis

    private static $FORM_REQUEST_TYPES = array('post', 'put', 'patch');
        // These requests normally use forms for submitting data.
        // Not using const for compatibility with PHP 5

    public static function generateFormSignature($session) {
        $forms_table = self::getFormsTable($session);

        if (count($forms_table) >= self::MAX_SIMULTANEOUS_FORMS) {
            $oldest_key = min(array_keys($forms_table));
            unset($forms_table[$oldest_key]);
        }

        $signature = $now = time();
        $forms_table[$signature] = ['expires' => $now + self::FORM_VALIDITY_PERIOD];
        self::setFormsTable($session, $forms_table);

        return $signature;
    }

    public function initialize(array $config = ['fascist' => false]) {
        if ($config) {
            $this->fascist_mode = (bool) $config['fascist'];
        } else {
            $this->fascist_mode = false;
        }
    }

    public function beforeFilter(Event $event) {
        $controller = $event->getSubject();
        $request = $controller->request;
        if (!$this->requestRequiresForm($request)) {
            return;
        }
        return $this->checkFormIntegrity($request);
    }

    public function beforeRedirect(Event $event) {
        // Form expiration is done here to avoid expiring a form that
        // failed to validate.
        $request = $event->getSubject()->request;
        $form_id = $request->getData('__form_signature');
        if (!$form_id)
            return;
        $session = $request->getSession();
        $forms_table = $this->getFormsTable($session);
        unset($forms_table[$form_id]);
        $this->setFormsTable($session, $forms_table);
    }

   private static function getFormsTable($session) {
        $forms_table = $session->read('Forms');
        if (!is_array($forms_table)) {
            $forms_table = [];
            $session->write('Forms', $forms_table);
        }
        return $forms_table;
    }

    private static function setFormsTable($session, $forms_table) {
        $session->write('Forms', $forms_table);
    }

    // Checks whether the request is supposed to submit data through
    // a form given its type (ie POST, PUT, and PATCH) and content type.
    private function requestRequiresForm($request) {
        $content_type = $request->getHeader('Content-Type');
        return $content_type === 'application/x-www-form-urlencoded'
                || $content_type === 'multipart/form-data'
                && array_search($request->getMethod(), self::$FORM_REQUEST_TYPES) >= 0;
    }

    // Checks whether the form in $request has not already been submitted.
    private function checkFormIntegrity($request) {
        $session = $request->getSession();
        $form_id = $request->getData('__form_signature');
        if ($form_id) {
            $forms_table = $this->getFormsTable($session);

            if (!array_key_exists($form_id, $forms_table)) {
                Log::write('info', __('Expired or non existent form {0} submitted by `{1}` from remote {2}',
                                      $form_id, $this->getLogin($session), $request->clientIp()));
                throw new DuplicateFormException('Form already submitted or expired.');
            }

            $form = $forms_table[$form_id];
            if ($form['expires'] < time()) {
                Log::write('info', __('Expired form {0} submitted by `{1}` from remote {2}',
                                      $form_id, $this->getLogin($session), $request->clientIp()));
                unset($forms_table[$form_id]);
                $this->setFormsTable($session, $forms_table);
                throw new DuplicateFormException('Form expired.');
            }
        } else if ($this->fascist_mode) {
            Log::write('info', __('Form without an id submitted by `{0}` from  remote {1}',
                                  $this->getLogin($session), $request->clientIp()));
            throw new DuplicateFormException('Form does not have an id.');
        }
    }

    private function getLogin($session) {
        $user = $session->read('Auth.User');
        if ($user) {
            return $user['username'];
        }
        return 'Anonymous User';
    }
}
