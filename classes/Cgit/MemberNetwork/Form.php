<?php

namespace Cgit\MemberNetwork;

abstract class Form
{
    /**
     * Form ID
     *
     * @var string
     */
    protected $id;

    /**
     * Fields
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Values
     *
     * @var array
     */
    protected $values = [];

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Default fields
     *
     * @var array
     */
    protected $defaultFields = [];

    /**
     * Recaptcha?
     *
     * @var boolean
     */
    protected $recaptcha = false;

    /**
     * Recaptcha public key
     *
     * @var string
     */
    protected $recaptchaPublicKey = '';

    /**
     * Recaptcha secret key
     *
     * @var string
     */
    protected $recaptchaSecretKey = '';

    /**
     * Construct
     *
     * @return void
     */
    final public function __construct()
    {
        $this->init();
        $this->sanitize();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function init()
    {
        // extend md
    }

    /**
     * Sanitize fields
     *
     * Start with the default form fields; apply filters to allow customization;
     * then restore any default fields that have been deleted.
     *
     * @return void
     */
    final protected function sanitize()
    {
        $fields = apply_filters('cgit_member_network_form_fields', $this->defaultFields, $this->id);
        $fields = array_merge($this->fields, $this->defaultFields);

        $this->fields = $fields;
    }

    /**
     * Validate fields
     *
     * @return void
     */
    final protected function validate()
    {
        foreach ($this->fields as $key => $field) {
            $this->validateField($key, $field);
        }

        $this->validateRecaptcha();

        $this->values = apply_filters('cgit_member_network_field_values', $this->values, $this->id);
        $this->errors = apply_filters('cgit_member_network_field_errors', $this->errors, $this->id);
    }

    /**
     * Submit form data
     *
     * @return void
     */
    final public function submit()
    {
        if (!$this->submitted()) {
            return;
        }

        $this->validate();

        if (!$this->completed()) {
            return;
        }

        $this->done();
    }

    /**
     * Has the form been submitted?
     *
     * @return boolean
     */
    final public function submitted()
    {
        $name = apply_filters('cgit_member_network_form_form_id', 'form_id');
        $submitted = isset($_POST[$name]) && $_POST[$name] == $this->id;

        return $submitted;
    }

    /**
     * Has the form been completed without errors?
     *
     * @return boolean
     */
    final public function completed()
    {
        return $this->submitted() && !$this->errors;
    }

    /**
     * Handle completed form data
     *
     * @return void
     */
    protected function done()
    {
        // extend me
    }

    /**
     * Return form ID
     *
     * @return string
     */
    final public function id()
    {
        return $this->id;
    }

    /**
     * Return field label
     *
     * @param string $key
     * @return mixed
     */
    final public function label($key)
    {
        if (!isset($this->fields[$key]) || !isset($this->fields[$key]['label'])) {
            return null;
        }

        return $this->fields[$key]['label'];
    }

    /**
     * Return field value
     *
     * @param string $key
     * @return mixed
     */
    final public function value($key)
    {
        return (isset($this->values[$key])) ? $this->values[$key] : null;
    }

    /**
     * Return field error
     *
     * @param string $key
     * @return string
     */
    final public function error($key)
    {
        return (isset($this->errors[$key])) ? $this->errors[$key] : null;
    }

    /**
     * Return formatted error message
     *
     * @param string $key
     * @return string
     */
    final public function errorMessageFragment($key)
    {
        $error = $this->error($key);

        if (!$error) {
            return '';
        }

        $filter = 'cgit_member_network_form_error_message_template';
        $template = apply_filters($filter, '<span class="error">%s</span>', $this->id);

        return sprintf($template, $error);
    }

    /**
     * Validate individual field
     *
     * @param string $key
     * @param array $field
     * @return void
     */
    final private function validateField($key, $field = [])
    {
        // Set value
        $value = isset($_POST[$key]) ? $_POST[$key] : null;
        $this->values[$key] = $value;

        // Skip validation if not required field
        if (!isset($field['required']) || !$field['required']) {
            return;
        }

        // Validate required field
        if (!$value) {
            $this->errors[$key] = 'required field';
        }

        // Validate by type
        if (isset($field['validate'])) {
            $method = 'validateFieldType' . ucfirst(strtolower($field['validate']));

            if (method_exists($this, $method)) {
                $this->$method($key, $value);
            }
        }
    }

    /**
     * Validate email field
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    final private function validateFieldTypeEmail($key, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $this->errors[$key] = 'invalid email address';
    }

    /**
     * Enable Recaptcha?
     *
     * @param boolean $enabled
     * @param string $public
     * @param string $secret
     * @return void
     */
    final public function enableRecaptcha($enabled, $public = null, $secret = null)
    {
        $this->recaptcha = (bool) $enabled;

        $this->setRecaptchaPublicKey($public);
        $this->setRecaptchaSecretKey($secret);
    }

    /**
     * Set Recaptcha public key
     *
     * @param string $key
     * @return void
     */
    final public function setRecaptchaPublicKey($key)
    {
        if (!is_string($key)) {
            return;
        }

        $this->recaptchaPublicKey = $key;
    }

    /**
     * Set Recaptcha secret key
     *
     * @param string $key
     * @return void
     */
    final public function setRecaptchaSecretKey($key)
    {
        if (!is_string($key)) {
            return;
        }

        $this->recaptchaSecretKey = $key;
    }

    /**
     * Render Recaptcha
     *
     * @param boolean $hide_errors
     * @return void
     */
    final public function renderRecaptcha($hide_errors = false)
    {
        if (!$this->hasRecaptcha()) {
            return;
        }

        ?>
        <div class="field -recaptcha">
            <script src="https://www.google.com/recaptcha/api.js"></script>
            <div class="g-recaptcha" data-sitekey="<?= $this->recaptchaPublicKey ?>"></div>

            <?php

            if (!$hide_errors) {
                echo $this->errorMessageFragment('recaptcha');
            }

            ?>
        </div>
        <?php
    }

    /**
     * Validate Recaptcha
     *
     * @return void
     */
    final private function validateRecaptcha()
    {
        if (!$this->hasRecaptcha()) {
            return;
        }

        $recaptcha = new \ReCaptcha\Recaptcha($this->recaptchaSecretKey);
        $recaptcha->setExpectedHostName($_SERVER['SERVER_NAME']);

        $response = $recaptcha->verify(
            $_POST['g-recaptcha-response'],
            $_SERVER['REMOTE_ADDR']
        );

        if ($response->isSuccess()) {
            return;
        }

        $this->errors['recaptcha'] = 'please confirm you are not a robot';
    }

    /**
     * Has Recaptcha?
     *
     * Return true if Recaptcha is available, enabled, and has both keys.
     *
     * @return boolean
     */
    final private function hasRecaptcha()
    {
        return class_exists('\\ReCaptcha\\Recaptcha') &&
            $this->recaptcha &&
            is_string($this->recaptchaPublicKey) &&
            is_string($this->recaptchaSecretKey) &&
            $this->recaptchaPublicKey != '' &&
            $this->recaptchaSecretKey != '';
    }
}
