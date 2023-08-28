<?php

namespace SUPT\StarterpackForms\Submission;

use Exception;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

use function SUPT\StarterpackForms\Helpers\slugify;
use function SUPT\StarterpackForms\Helpers\get_forms_secret;
use function SUPT\StarterpackForms\Helpers\is_form_post_type;
use function SUPT\StarterpackForms\Helpers\get_hcaptcha_secret;
use function SUPT\StarterpackForms\Helpers\rearrange_upload_array;
use function SUPT\StarterpackForms\Helpers\supt_get_extension_mimetype;
use function SUPT\StarterpackForms\Helpers\get_template_part as spckforms_get_template_part;
use function SUPT\StarterpackForms\Helpers\rrmdir;

use const SUPT\StarterpackForms\Type\META_KEY_FIELDS_NAME;
use const SUPT\StarterpackForms\ResultList\META_KEY_FORM_SUBMISSION;
use const SUPT\StarterpackForms\ResultList\SETTING_SAVE_TO_DB;
use const SUPT\StarterpackForms\ResultList\SETTING_SAVE_TO_DB_N_SEND_NOTIF;

new Submission();

/**
 * handle the form submission according to FormType fields
 */
class Submission {

	const ACTION_PARAM = 'spckforms-action';
	const SECRET_PARAM = 'spckforms-secret';
	const ACTION_SUBMIT_NAME  = 'submit-form';
	const ACTION_UPLOAD_NAME  = 'upload-file';
	const HCAPTCHA_VERIFY_URL = 'https://hcaptcha.com/siteverify';

	const UPLOAD_DIR_NAME = 'form-files';

	private $wpUploadDir;
	private $uploadDirPath;

	private $status = 400;
	private $data   = false;
	private $errors = false;

	private $form_id;

	private $params;
	private $fields;
	private $payload;
	private $payload_attrs;
	private $uploadId = null;

	private $userEmail;

	function __construct() {
		add_action( 'init', array($this, 'handle_submit') );
		add_action( 'init', array($this, 'handle_file_upload') );
	}

	public function handle_file_upload() {
		if ( !(isset($_GET[self::ACTION_PARAM]) && $_GET[self::ACTION_PARAM] === self::ACTION_UPLOAD_NAME) ) {
			return;
		}

		$this->secret  = $_GET[self::SECRET_PARAM];
		if ( empty($this->secret) || $this->secret !== get_forms_secret() ) {
			error_log('FORM FILE UPLOAD: Unauthorized');
			$this->status = 401;
			$this->response();
			return;
		}

		$this->params  = $_POST;
		$this->set_upload_dirs($this->params['id'] .'/'. $this->params['key']);

		$filedata = $this->decode_chunk( $this->params['data'] );
		if ( false === $filedata ) {
			error_log('FORM FILE UPLOAD: file data not decode correctly');
			$this->status = 500;
			$this->response();
		}

		$filepath = trailingslashit($this->uploadDirPath) . $this->params['name'];
		file_put_contents( $filepath, $filedata, FILE_APPEND );

		$this->status = 200;
		$this->response();
	}

	/**
	 * @source https://deliciousbrains.com/using-javascript-file-api-to-avoid-file-upload-limits/
	 */
	public function decode_chunk( $data ) {
		$data = explode( ';base64,', $data );

		if ( ! is_array( $data ) || ! isset( $data[1] ) ) {
			return false;
		}

		$data = base64_decode( $data[1] );
		if ( ! $data ) {
			return false;
		}

		return $data;
	}

	public function handle_submit() {
		if ( !(isset($_GET[self::ACTION_PARAM]) && $_GET[self::ACTION_PARAM] === self::ACTION_SUBMIT_NAME) ) {
			return;
		}

		$this->secret   = $_GET[self::SECRET_PARAM];
		$this->params   = $_POST;
		$this->form_id  = intval($this->params['id']);
		$this->uploadId = $this->params['uploadId'] ?? null;

		unset($this->params['uploadId']); // make sure to unset this;

		$this->success_message = get_field( 'form_success_message', $this->form_id );
		$this->error_message   = get_field( 'form_error_message', $this->form_id );

		// If options empty retrieve global options
		if ( empty($this->success_message) )
			$this->success_message = get_field( 'form_success_message', 'options') ?? 'Your message has been sent successfully.';
		if ( empty($this->error_message) )
			$this->error_message = get_field( 'form_error_message', 'options') ?? 'Oups an error happened. Please try again later.';

		// bail if secret doesn't matches
		if ( empty($this->secret) || $this->secret !== get_forms_secret() ) {
			error_log('FORM SUBMISSION: Unauthorized');
			$this->status = 401;
			$this->errors['__global'] = true;
			$this->response();
			return;
		}

		// bail if form doesn't exist
		if ( !is_form_post_type($this->form_id) || get_post_status($this->form_id) !== 'publish' ) {
			error_log('FORM SUBMISSION: Form not found');
			$this->status = 404;
			$this->errors['__global'] = true;
			$this->response();
			return;
		}

		// Validate hCaptcha and stop process if any error
		if ( ! $this->validateHCaptcha() ) {
			error_log('FORM SUBMISSION: Unable to verify hCaptcha token');
			$this->errors['__global'] = true;
			$this->response();
			return;
		}

		$validator = Validation::createValidator();

		$this->fields = (array)get_field(META_KEY_FIELDS_NAME, $this->form_id);
		// Filter fields to remove 'supt/form-section-breaker' one as we don't want to send any data related to this field
		$this->fields = array_filter($this->fields, function($field) {
			return $field['block'] !== 'supt/form-section-breaker';
		});

		$this->set_upload_dirs($this->uploadId);
		$this->payload = $this->get_payload();

		// register the fields constraints
		$this->fieldsConstraints = $this->get_fields_constraints();

		$violations = $validator->validate($this->payload, new Assert\Collection ( $this->fieldsConstraints ));
		if ( count(($violations)) > 0 ) {
			foreach ($violations as $violation) {
				$property = preg_replace( "/(\[|\]|--\d+)/i", '', $violation->getPropertyPath() );
				if (!isset($this->errors[$property])) $this->errors[$property] = [];
				$this->errors[$property][] = $violation->getMessage();
			}

			// Delete uploaded files
			if ( !empty($this->uploadId) && file_exists($this->uploadDirPath) ) {
				rrmdir( $this->uploadDirPath );
			}

			$this->response();
			return;
		}

		$this->saved_to_db = $this->maybe_save_submission_to_db( $this->form_id, array_merge($this->payload, ['timestamp' => time()]) );

		if ( !($send_emails = $this->send_emails()) && ($sould_send_notif = $this->should_send_notif()) ) { // order is important! we want to run send_emails() all the time, but fail only if email submission is required
			error_log('FORM SUBMISSION: Unenable to send emails');
			$this->errors['__global'] = true;
			$this->status = 500;
		}
		else {
			$this->status = 200;
			$this->data = [
				'message' => $this->success_message
			];
		}

		$this->response();
	}

	private function set_upload_dirs($extra_path = null) {
		$this->wpUploadDir   = wp_upload_dir();
		$this->uploadDirPath = trailingslashit($this->wpUploadDir['basedir']). trailingslashit(self::UPLOAD_DIR_NAME) . $extra_path ?? '';
		$this->uploadDirURL  = trailingslashit($this->wpUploadDir['baseurl']). trailingslashit(self::UPLOAD_DIR_NAME) . $extra_path ?? '';

		// make sure the folder is created
		if ( ! file_exists($this->uploadDirPath) ) @mkdir($this->uploadDirPath, 0777, true);
	}

	private function get_fields_constraints() {
		$constraints = [];
		foreach ($this->fields as $field) {
			$block = $field['block'];
			$attrs = $field['attributes'];
			$name  = $attrs['name'];

			if ( strpos($block, 'email') !== false) {
				$constraints[$name] = new Assert\Email(['message' => _x('Please enter a valid email address.', 'SUPT Forms', 'spckforms')]);
			}

			else if ( strpos($block, 'file') ) {
				for ($i=0; $i < ($attrs['nbFiles'] ?? 1); $i++) {
					$constrain_key = "$name--$i";

					$constraints[$constrain_key][] = (isset($attrs['required']) && $attrs['required'] && $i === 0)
						? new Assert\NotBlank(['message' => _x('Please upload a file.', 'SUPT Forms', 'spckforms')])
						: new Assert\Optional();

					$constraints[$constrain_key][] = new Assert\File([
						'mimeTypes'        => $this->parse_file_accept($attrs['accept'] ?? null),
						// translators: %s is the types of files allowed
						'mimeTypesMessage' => sprintf(__('The file is invalid. Allowed files are %s.', 'spckforms'), $attrs['accept']),

						'maxSize'          => (empty($attrs['maxFilesize']) ? '' : "{$attrs['maxFilesize']}M"),
						// translators: %s is the maximum size of a file
						'maxSizeMessage'   => sprintf(__('The file is too large. Maximum file size %sMB.', 'spckforms'), $attrs['maxFilesize']),
					]);
				}

				continue;
			}

			// else if ( in_array($block, [
			// 	'supt/checkbox',
			// 	'supt/input-checkbox',
			// 	'supt/input-option-radio',
			// 	'supt/input-radio',
			// 	'supt/input-select',
			// 	'supt/input-text',
			// 	'supt/input-textarea',
			// 	'supt/radio',
			// ]) ) {
			// 	// do nothing special (but keep these fields)
			// }

			// else {
			// 	// no constraint for this field
			// 	// -> validation will fail since this field was not expected
			// 	continue;
			// }

			if ( isset($attrs['required']) && $attrs['required'] ) {
				$notNull = new Assert\NotBlank(['message' => _x('Please fill out this field.', 'SUPT Forms', 'spckforms')]);

				if ( !isset($constraints[$name])  ) $constraints[$name] = $notNull;
				else $constraints[$name] = [ $notNull, $constraints[$name] ];
			}

			if ( !isset($constraints[$name]) ) {
				$constraints[$name] = new Assert\Optional();
			}
		}
		// Add filter for constraints
		$constraints = apply_filters( 'spckforms_constraints', $constraints, $this->form_id, $this->fields);

		return $constraints;
	}

	private function get_payload() {
		$payload = [];

	  foreach ($this->fields as $i => $field) {
			$block         = $field['block'];
			$attrs         = $field['attributes'];
			$name          = $attrs['name'];
			$attrs['i']    = $i;
			$attrs['type'] = 'text';

			if ( strpos($block, 'file') ) {
				$files = $this->params[$name];

				for ($j=0; $j < ($attrs['nbFiles'] ?? 1); $j++) {
					$value        = NULL;
					$payload_data = NULL;
					$payload_name = "$name--$j";

					if ( isset($files[$j]) ) {
						$filename = $files[$j];
						$filepath = trailingslashit($this->uploadDirPath) . "$name/$filename";
						$fileurl  = trailingslashit($this->uploadDirURL)  . "$name/$filename";

						// Guess the real extension from it's mime type
						$mimetypes = new MimeTypes();
						$types = $mimetypes->guessMimeType($filepath);
						if ( empty($types) ) {
							error_log('FORM SUBMISSION: Unable to guess the file extension');
							continue;
						}

						$payload_data  = $filepath;
						$attrs['type'] = 'file';
						$value         = [
							'path' => $filepath,
							'url'  => $fileurl,
							'name' => basename($filepath),
							'size' => size_format(filesize($filepath), 2),
						];
					}

					$payload[$payload_name] = $payload_data;
					if (!empty($value)) $attrs['value'][] = $value;
				}
			}

			// input-text, input-email, input-textarea, input-checkbox, input-radio, input-select
			else {
				if ( strpos($block, 'email') !== false ) {
					if (empty($this->userEmail) && !empty($this->params[$name])) $this->userEmail = $this->params[$name];
				}

				$payload[$name] = $attrs['value'] = (isset($this->params[$name]) ? $this->params[$name] : NULL);
			}

			$this->payload_attrs[$name] = $attrs;
		}

		return $payload;
	}

	/**
	 * Send the repsonse if ajax
	 * or set the status in global variable
	 */
	private function response() {
		status_header( $this->status );

		if ( $this->status === 200 ) wp_send_json_success( $this->data );
		else {
			if ( isset($this->errors['__global']) && true === $this->errors['__global'] )
				$this->errors['__global'] = $this->error_message;

			wp_send_json_error([ 'errors' => $this->errors ]);
		}
		die();
	}

	private function validateHCaptcha() {
		$hcaptcha_secret = get_hcaptcha_secret();
		if ( false === ($hcaptcha_secret = get_hcaptcha_secret()) ) {
			return true;
		}

		// Init the request object
		$ch = curl_init();

		// Set the request parameters
		curl_setopt_array( $ch, array(
			CURLOPT_URL							=> self::HCAPTCHA_VERIFY_URL,
			CURLOPT_HEADER					=> false,
			CURLOPT_POST						=> true,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_POSTFIELDS			=> [
				"secret"		=> $hcaptcha_secret,
				"response"	=> $this->params['_tk'],
				"remoteip"	=> $_SERVER['REMOTE_ADDR'],
			]
		) );

		// Execute & close the request
		$response = json_decode( curl_exec($ch) );
		curl_close($ch);

		return $response->success;
	}

	private function get_confirmation_email_params() {
		// Retrieve options from form
		$fromName  = get_field( 'form_name_from', $this->form_id );
		$fromEmail = get_field( 'form_email_from', $this->form_id );
		$autoreply = get_field( 'email_autoreply', $this->form_id );

		// If options empty retrieve global options
		if ( empty($fromName) )  $fromName  = get_field( 'form_name_from', 'options' );
		if ( empty($fromEmail) ) $fromEmail = get_field( 'form_email_from', 'options' );

		if ( empty($autoreply['subject']) || empty($autoreply['template']) ) {
			$optAutoreply = get_field( 'email_autoreply', 'options' );
			if ( empty($autoreply['subject']) )  $autoreply['subject'] = $optAutoreply['subject'];
			if ( empty($autoreply['template']) ) $autoreply['template'] = $optAutoreply['template'];
		}

		$data = [];
		foreach ($this->payload_attrs as $name => $attrs) {
			if ( isset($attrs['type']) && $attrs['type'] === 'file' ) continue;
			$data["{{$name}}"] = $attrs['value'];
			$data["{{ $name }}"] = $attrs['value']; // make sure it matches with/without spaces
		}

		$context = [
			'subject' => $autoreply['subject'],
			'content' => str_replace( array_keys($data), array_values($data), $autoreply['template'] ),
			'site'    => $this->get_site_context(),
		];

		return [
			'to'      => $this->userEmail,
			'from'    => ( empty($fromName) ? $fromEmail : "$fromName <$fromEmail>" ),
			'subject' => $autoreply['subject'],
			'body'    => spckforms_get_template_part( 'email-user-confirmation.php', $context, false ),
		];
	}

	private function get_notification_email_params() {

		$to        = get_field( 'form_email_to', $this->form_id );
		$fromName  = get_field( 'form_name_from', $this->form_id );
		$fromEmail = get_field( 'form_email_from', $this->form_id );

		// If options empty retrieve global options
		if ( empty($to) )        $to = get_field( 'form_email_to', 'options' );
		if ( empty($fromName) )  $fromName  = get_field( 'form_name_from', 'options' );
		if ( empty($fromEmail) ) $fromEmail = get_field( 'form_email_from', 'options' );

		$form_name = get_the_title( $this->form_id );

		$context = [
			'data'     => $this->payload_attrs,
			'site'     => $this->get_site_context(),
			'form'     => [
				'link'  => admin_url(),
				'title' => $form_name,
			]
		];

		return [
			'to'      => $to,
			'from'    => ( empty($fromName) ? $fromEmail : "$fromName <$fromEmail>" ),
			// translators: %s is the name of the form
			'subject' => sprintf( _x('Form submission from "%s"', 'SUPT Forms', 'spckforms'), $form_name ),
			'body'    => spckforms_get_template_part( 'email-notification.php', $context, false ),
		];
	}

	private function send_emails() {
		$sent = false;

		try {
			// Notification
			if ($this->should_send_notif()) {
				$sent = $this->send_mail( $this->get_notification_email_params() );
			}

			// Confirmation Auto Reply
			if ( !empty($this->userEmail) ) {
				$sent = ($sent && $this->send_mail( $this->get_confirmation_email_params() ));
			}
		}
		catch (Exception $e) {
			error_log('FORM SUBMISSION: Exception thrown while sending emails: ' . $e);
		}

		return $sent;
	}

	/**
	 * Send an email using the given params
	 * Uses the `wp_mail` function
	 *
	 * @param array $params [ to, from, subject, body ]
	 * @return bool
	 */
	private function send_mail($params) {
		$headers = array(
			"From: {$params['from']}",
			'Content-Type: text/html; charset="UTF-8";'
		);

		// Remove weird chars like `&#8211;`
		$subject = preg_replace("/&[^;]+;\s?/", '', $params['subject']);

		$mail_sent = wp_mail( $params['to'], $subject, $params['body'], $headers );

		return $mail_sent;
	}

	function should_send_notif() {
		$send_notif = true;
		if ( get_field(SETTING_SAVE_TO_DB, $this->form_id) ) {
			$send_notif = get_field( SETTING_SAVE_TO_DB_N_SEND_NOTIF, $this->form_id);
		}
		return $send_notif;
	}

	private function maybe_save_submission_to_db() {
		if ( get_field( SETTING_SAVE_TO_DB, $this->form_id) ) {

			$data = [];
			foreach ($this->payload_attrs as $name => $attrs) {
				$data[$name] = $attrs['value'];
			}
			add_post_meta( $this->form_id, META_KEY_FORM_SUBMISSION, array_merge($data, ['timestamp' => time()]) );

			return true;
		}

		return false;
	}

	private function parse_file_accept($accept = '') {
		return array_reduce(explode(',', $accept), function($mimes, $ext) {
			$mimetype = supt_get_extension_mimetype(trim($ext, " .\t\n\r\0\x0B"));
			if ( !empty($mimetype) ) {
				$mimes = array_merge($mimes, is_array($mimetype) ? $mimetype : [$mimetype]);
			}
			return $mimes;
		}, []);
	}

	private function get_site_context() {
		$logo = null;
		$logo_id =  get_theme_mod('custom_logo');
		if ( !empty($logo_id) ) {
			$logo_attr = wp_get_attachment_image_src($logo_id, 'full');
			$logo = [
				'src'    => $logo_attr[0],
				'width'  => $logo_attr[1],
				'height' => $logo_attr[2],
				'alt'    => get_bloginfo('name'),
			];
		}

		return [
			'name'    => get_bloginfo('name'),
			'url'     => apply_filters( 'spckforms_site_url', get_site_url() ),
			'logo'    => $logo,
		];
	}
}
