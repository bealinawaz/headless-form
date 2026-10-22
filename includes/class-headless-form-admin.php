<?php
/**
 * Settings screen and developer guide.
 *
 * @package Headless_Form
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin pages under the Form Submissions menu.
 */
final class Headless_Form_Admin {

	const SETTINGS_PAGE = 'headless-form-settings';
	const GUIDE_PAGE    = 'headless-form-guide';

	/**
	 * Registers hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_pages' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( HEADLESS_FORM_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Adds the Settings and Developer guide submenus.
	 */
	public static function add_pages() {
		$parent = 'edit.php?post_type=' . Headless_Form_Submissions::POST_TYPE;
		add_submenu_page( $parent, __( 'Headless Form Settings', 'headless-form' ), __( 'Settings', 'headless-form' ), 'manage_options', self::SETTINGS_PAGE, array( __CLASS__, 'render_settings' ) );
		add_submenu_page( $parent, __( 'Headless Form Developer Guide', 'headless-form' ), __( 'Developer guide', 'headless-form' ), 'manage_options', self::GUIDE_PAGE, array( __CLASS__, 'render_guide' ) );
	}

	/**
	 * Registers the settings option.
	 */
	public static function register_setting() {
		register_setting(
			Headless_Form_Settings::GROUP,
			Headless_Form_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Headless_Form_Settings', 'sanitize' ),
			)
		);
	}

	/**
	 * Loads the guide's copy buttons on the guide page only.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		if ( false === strpos( $hook, self::GUIDE_PAGE ) ) {
			return;
		}
		$url = plugin_dir_url( HEADLESS_FORM_FILE ) . 'assets/';
		wp_enqueue_style( 'headless-form-admin', $url . 'admin.css', array(), HEADLESS_FORM_VERSION );
		wp_enqueue_script( 'headless-form-admin', $url . 'admin.js', array( 'wp-i18n' ), HEADLESS_FORM_VERSION, true );
		wp_set_script_translations( 'headless-form-admin', 'headless-form' );
	}

	/**
	 * Adds Settings and Guide links on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$base = admin_url( 'edit.php?post_type=' . Headless_Form_Submissions::POST_TYPE . '&page=' );
		array_unshift(
			$links,
			'<a href="' . esc_url( $base . self::SETTINGS_PAGE ) . '">' . esc_html__( 'Settings', 'headless-form' ) . '</a>',
			'<a href="' . esc_url( $base . self::GUIDE_PAGE ) . '">' . esc_html__( 'Guide', 'headless-form' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Settings field name.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return Headless_Form_Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Renders the settings page.
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = Headless_Form_Settings::get();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Headless Form Settings', 'headless-form' ); ?></h1>
			<p>
				<?php esc_html_e( 'Your endpoint:', 'headless-form' ); ?>
				<code><?php echo esc_html( Headless_Form_API::endpoint() ); ?></code>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Headless_Form_Submissions::POST_TYPE . '&page=' . self::GUIDE_PAGE ) ); ?>"><?php esc_html_e( 'How to connect a form', 'headless-form' ); ?></a>
			</p>
			<form action="options.php" method="post">
				<?php settings_fields( Headless_Form_Settings::GROUP ); ?>

				<h2><?php esc_html_e( 'Notifications', 'headless-form' ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Email notifications', 'headless-form' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::name( 'email_notifications' ) ); ?>" value="1" <?php checked( $s['email_notifications'] ); ?> /> <?php esc_html_e( 'Email each submission', 'headless-form' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><label for="hdform-recipient"><?php esc_html_e( 'Send to', 'headless-form' ); ?></label></th>
						<td><input id="hdform-recipient" class="regular-text" type="email" name="<?php echo esc_attr( self::name( 'recipient' ) ); ?>" value="<?php echo esc_attr( $s['recipient'] ); ?>" required /></td>
					</tr>
					<tr>
						<th scope="row"><label for="hdform-subject"><?php esc_html_e( 'Subject', 'headless-form' ); ?></label></th>
						<td>
							<input id="hdform-subject" class="regular-text" type="text" name="<?php echo esc_attr( self::name( 'email_subject' ) ); ?>" value="<?php echo esc_attr( $s['email_subject'] ); ?>" required />
							<p class="description"><?php esc_html_e( 'Named forms add their name in brackets, for example [newsletter].', 'headless-form' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hdform-reply"><?php esc_html_e( 'Reply-to field', 'headless-form' ); ?></label></th>
						<td>
							<input id="hdform-reply" class="regular-text code" type="text" name="<?php echo esc_attr( self::name( 'reply_to_field' ) ); ?>" value="<?php echo esc_attr( $s['reply_to_field'] ); ?>" />
							<p class="description"><?php esc_html_e( 'The field that holds the sender\'s email address. Replying to a notification replies to the sender, and privacy requests find submissions by this address.', 'headless-form' ); ?></p>
						</td>
					</tr>
				</tbody></table>

				<h2><?php esc_html_e( 'Security', 'headless-form' ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><label for="hdform-origins"><?php esc_html_e( 'Allowed websites', 'headless-form' ); ?></label></th>
						<td>
							<textarea id="hdform-origins" class="large-text code" rows="4" name="<?php echo esc_attr( self::name( 'allowed_origins' ) ); ?>" placeholder="https://example.com"><?php echo esc_textarea( implode( "\n", $s['allowed_origins'] ) ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One origin per line, such as https://example.com. Browsers on other websites are refused. Leave empty to accept submissions from any website.', 'headless-form' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="hdform-honeypot"><?php esc_html_e( 'Spam trap field', 'headless-form' ); ?></label></th>
						<td>
							<input id="hdform-honeypot" class="regular-text code" type="text" name="<?php echo esc_attr( self::name( 'honeypot_field' ) ); ?>" value="<?php echo esc_attr( $s['honeypot_field'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Add a hidden field with this name to your form. People leave it empty; bots fill it in, and those submissions are rejected.', 'headless-form' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Rate limit', 'headless-form' ); ?></th>
						<td>
							<?php
							printf(
								/* translators: 1: number input, 2: minutes input. */
								esc_html__( '%1$s submissions per visitor every %2$s minutes', 'headless-form' ),
								'<input type="number" class="small-text" min="1" max="100" name="' . esc_attr( self::name( 'rate_limit' ) ) . '" value="' . esc_attr( (string) $s['rate_limit'] ) . '" aria-label="' . esc_attr__( 'Submissions', 'headless-form' ) . '" />',
								'<input type="number" class="small-text" min="1" max="1440" name="' . esc_attr( self::name( 'rate_window_minutes' ) ) . '" value="' . esc_attr( (string) $s['rate_window_minutes'] ) . '" aria-label="' . esc_attr__( 'Minutes', 'headless-form' ) . '" />'
							);
							?>
						</td>
					</tr>
				</tbody></table>

				<h2><?php esc_html_e( 'Privacy', 'headless-form' ); ?></h2>
				<table class="form-table" role="presentation"><tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'IP address', 'headless-form' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::name( 'store_ip' ) ); ?>" value="1" <?php checked( $s['store_ip'] ); ?> /> <?php esc_html_e( 'Save the visitor\'s IP address with each submission', 'headless-form' ); ?></label></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Location', 'headless-form' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::name( 'location_lookup' ) ); ?>" value="1" <?php checked( $s['location_lookup'] ); ?> /> <?php esc_html_e( 'Look up the approximate city and country', 'headless-form' ); ?></label>
							<p class="description">
								<?php
								printf(
									/* translators: %s: link to ipwho.is. */
									esc_html__( 'Sends the visitor\'s IP address to %s. Requires saving IP addresses. Mention this in your privacy policy.', 'headless-form' ),
									'<a href="https://ipwho.is/" target="_blank" rel="noopener">ipwho.is</a>'
								);
								?>
							</p>
						</td>
					</tr>
				</tbody></table>

				<h2><?php esc_html_e( 'Messages', 'headless-form' ); ?></h2>
				<p><?php esc_html_e( 'Returned in the message field of each response, ready to show to visitors.', 'headless-form' ); ?></p>
				<table class="form-table" role="presentation"><tbody>
					<?php foreach ( Headless_Form_Settings::message_labels() as $key => $label ) : ?>
						<tr>
							<th scope="row"><label for="hdform-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td><textarea id="hdform-<?php echo esc_attr( $key ); ?>" class="large-text" rows="2" name="<?php echo esc_attr( self::name( $key ) ); ?>"><?php echo esc_textarea( $s[ $key ] ); ?></textarea></td>
						</tr>
					<?php endforeach; ?>
				</tbody></table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders a code block with a copy button.
	 *
	 * @param string $language Label.
	 * @param string $code     Code.
	 */
	private static function code( $language, $code ) {
		?>
		<div class="hdform-code">
			<div class="hdform-code__bar">
				<span><?php echo esc_html( $language ); ?></span>
				<button type="button" class="button button-small hdform-copy"><?php esc_html_e( 'Copy', 'headless-form' ); ?></button>
			</div>
			<pre><code><?php echo esc_html( trim( $code ) ); ?></code></pre>
		</div>
		<?php
	}

	/**
	 * Renders the developer guide.
	 */
	public static function render_guide() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s        = Headless_Form_Settings::get();
		$endpoint = Headless_Form_API::endpoint();
		$named    = Headless_Form_API::endpoint( 'newsletter' );
		$honeypot = '' !== $s['honeypot_field'] ? $s['honeypot_field'] : '_gotcha';
		$site     = $s['allowed_origins'] ? $s['allowed_origins'][0] : 'https://example.com';
		?>
		<div class="wrap hdform-guide">
			<h1><?php esc_html_e( 'Connect a form', 'headless-form' ); ?></h1>
			<p><?php esc_html_e( 'Send any form to WordPress from a static site, React, Next.js, Vue, a mobile app, or a server. Every field you send is saved and emailed, with no form builder to set up.', 'headless-form' ); ?></p>

			<table class="widefat striped hdform-guide__table"><tbody>
				<tr><th scope="row"><?php esc_html_e( 'Endpoint', 'headless-form' ); ?></th><td><code><?php echo esc_html( $endpoint ); ?></code></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Named form', 'headless-form' ); ?></th><td><code><?php echo esc_html( $named ); ?></code> <?php esc_html_e( 'Use lowercase letters, numbers, hyphens, and underscores. Filter submissions by form name.', 'headless-form' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Method', 'headless-form' ); ?></th><td><code>POST</code></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Body', 'headless-form' ); ?></th><td><?php esc_html_e( 'A JSON object, or a regular form post (application/x-www-form-urlencoded or multipart/form-data). Files are not accepted.', 'headless-form' ); ?></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Authentication', 'headless-form' ); ?></th><td><?php esc_html_e( 'None. It is a public endpoint; restrict it to your websites under Settings.', 'headless-form' ); ?></td></tr>
			</tbody></table>

			<h2><?php esc_html_e( 'JavaScript (any framework)', 'headless-form' ); ?></h2>
			<?php
			self::code(
				'JavaScript',
				implode(
					"\n",
					array(
						'const form = document.querySelector("#contact-form");',
						'',
						'form.addEventListener("submit", async (event) => {',
						'  event.preventDefault();',
						'  const data = Object.fromEntries(new FormData(form));',
						'  data.__context = { page_url: location.href, referrer: document.referrer };',
						'',
						'  const response = await fetch("' . $endpoint . '", {',
						'    method: "POST",',
						'    headers: { "Content-Type": "application/json" },',
						'    body: JSON.stringify(data),',
						'  });',
						'  const result = await response.json();',
						'  // result.message is ready to show. Status 200 and 202 both mean the submission was saved.',
						'  alert(result.message);',
						'  if (response.ok) form.reset();',
						'});',
					)
				)
			);
			?>
			<p><?php esc_html_e( 'Values can be text, numbers, booleans, arrays (for checkbox groups), or nested objects.', 'headless-form' ); ?></p>

			<h2><?php esc_html_e( 'Plain HTML, no JavaScript', 'headless-form' ); ?></h2>
			<p><?php esc_html_e( 'Add a __redirect field and the visitor returns to that page with ?form_status=success or ?form_status=error. Redirects only go to your allowed websites or this site.', 'headless-form' ); ?></p>
			<?php
			self::code(
				'HTML',
				implode(
					"\n",
					array(
						'<form action="' . $endpoint . '" method="post">',
						'  <input name="name" autocomplete="name" required>',
						'  <input name="email" type="email" autocomplete="email" required>',
						'  <textarea name="message" required></textarea>',
						'',
						'  <!-- Spam trap: hidden from people, left empty. -->',
						'  <input name="' . $honeypot . '" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">',
						'  <input type="hidden" name="__redirect" value="' . $site . '/thank-you/">',
						'',
						'  <button type="submit">Send</button>',
						'</form>',
					)
				)
			);
			?>

			<h2><?php esc_html_e( 'From a server', 'headless-form' ); ?></h2>
			<?php
			self::code(
				'cURL',
				implode(
					"\n",
					array(
						'curl -X POST \'' . $endpoint . '\' \\',
						'  -H \'Content-Type: application/json\' \\',
						'  -d \'{"name": "Alex", "email": "alex@example.com", "message": "Hello"}\'',
					)
				)
			);
			?>
			<p><?php esc_html_e( 'Server requests send no Origin header, so they are refused when the allowed websites list is not empty.', 'headless-form' ); ?></p>

			<h2><?php esc_html_e( 'Responses', 'headless-form' ); ?></h2>
			<table class="widefat striped hdform-guide__table"><tbody>
				<tr><th scope="row"><code>200</code></th><td><?php esc_html_e( 'Saved and emailed.', 'headless-form' ); ?> <code>{"success": true, "message": "…", "email_status": "sent"}</code></td></tr>
				<tr><th scope="row"><code>202</code></th><td><?php esc_html_e( 'Saved, but the email could not be sent. Treat it as success.', 'headless-form' ); ?></td></tr>
				<tr><th scope="row"><code>400</code></th><td><?php esc_html_e( 'Invalid data, or the spam trap was filled in.', 'headless-form' ); ?></td></tr>
				<tr><th scope="row"><code>403</code></th><td><?php esc_html_e( 'The website is not in the allowed list.', 'headless-form' ); ?></td></tr>
				<tr><th scope="row"><code>429</code></th><td><?php esc_html_e( 'Rate limit reached.', 'headless-form' ); ?></td></tr>
			</tbody></table>
			<p><?php esc_html_e( 'Errors return JSON with a message field.', 'headless-form' ); ?></p>

			<h2><?php esc_html_e( 'Limits', 'headless-form' ); ?></h2>
			<ul class="ul-disc">
				<?php /* translators: 1: maximum fields, 2: maximum bytes. */ ?>
				<li><?php echo esc_html( sprintf( __( 'Up to %1$d fields and %2$s bytes of data per submission.', 'headless-form' ), Headless_Form_API::MAX_FIELDS, number_format_i18n( Headless_Form_API::MAX_BYTES ) ) ); ?></li>
				<?php /* translators: %d: maximum characters. */ ?>
				<li><?php echo esc_html( sprintf( __( 'Text values up to %d characters. Field names up to 100 characters.', 'headless-form' ), Headless_Form_API::MAX_TEXT ) ); ?></li>
				<li><?php esc_html_e( 'Field names starting with __ are reserved and are not saved.', 'headless-form' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'For developers', 'headless-form' ); ?></h2>
			<ul class="ul-disc">
				<li><code>headless_form_fields</code> — <?php esc_html_e( 'filter the fields before saving, or return a WP_Error to reject the submission.', 'headless-form' ); ?></li>
				<li><code>headless_form_submission_saved</code> — <?php esc_html_e( 'action after saving, for webhooks, CRMs, or Slack.', 'headless-form' ); ?></li>
				<li><code>headless_form_email</code> — <?php esc_html_e( 'filter the notification email.', 'headless-form' ); ?></li>
				<li><code>headless_form_client_ip</code> — <?php esc_html_e( 'filter the visitor IP address, for sites behind a proxy or CDN.', 'headless-form' ); ?></li>
			</ul>
		</div>
		<?php
	}
}
