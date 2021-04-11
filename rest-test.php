<?php
/**
 * Plugin Name: Simple REST API Tester
 * Plugin URI:
 * Description: Allows to send REST requests and see response
 * Version: 0.1
 * Author: bo2
 *
 * @package rest-test
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Exit if accessed directly.
	exit;
}

/**
 * Class RestTestPlugin.
 */
class RestTestPlugin {
	/**
	 * Registers plugin.
	 */
	public function load() {
		add_action( 'admin_menu', array( $this, 'add_rest_test_menu_item' ) );
	}

	/**
	 * Adds plugin menu item.
	 */
	public function add_rest_test_menu_item() {
		add_menu_page(
			'REST Test',
			'REST Test',
			'edit_posts',
			'rest_test',
			array( $this, 'rest_test_main_page' )
		);
	}

	/**
	 * Renders main plugin page.
	 */
	public function rest_test_main_page() {
		?>
		<style>
			pre#rt-response {outline: 1px solid #ccc; padding: 5px; margin: 5px; }
			.string { color: green; }
			.number { color: darkorange; }
			.boolean { color: blue; }
			.null { color: magenta; }
			.key { color: red; }
		</style>
		<div class="wrap">
			<h1>Simple REST API Tester</h1>
			<form>
				<table>
					<tr>
						<td>Method:</td>
						<td>
							<select id="rt-method" name="method">
								<option>GET</option>
								<option>POST</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>URL:</td>
						<td><input id="rt-url" name="url" style="width: 400px;" value="<?php echo esc_attr( get_rest_url() ); ?>"/></td>
					</tr>
					<tr>
						<td>Body:</td>
						<td><textarea id="rt-body" style="width: 400px; height: 200px"></textarea></td>
					</tr>
					<tr>
						<td></td>
						<td><button id="rt-button" type="button">Send</button></td>
					</tr>
				</table>
			</form>
			<pre id="rt-response"></pre>
		</div>
		<script>
			function syntaxHighlight(json) {
				json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
				return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
					var cls = 'number';
					if (/^"/.test(match)) {
						if (/:$/.test(match)) {
							cls = 'key';
						} else {
							cls = 'string';
						}
					} else if (/true|false/.test(match)) {
						cls = 'boolean';
					} else if (/null/.test(match)) {
						cls = 'null';
					}
					return '<span class="' + cls + '">' + match + '</span>';
				});
			}

			function sendData() {
				const XHR = new XMLHttpRequest();
				XHR.addEventListener( 'load', function(event) {
					let j = JSON.parse( XHR.response );
					document.getElementById( 'rt-response' ).innerHTML = syntaxHighlight( JSON.stringify(j, undefined, 4 ));
				} );
				XHR.addEventListener( 'error', function(event) {
					alert('API call error');
				} );

				XHR.open( document.getElementById('rt-method').value, document.getElementById('rt-url').value );
				XHR.setRequestHeader( 'Content-Type', 'application/json' );
				XHR.setRequestHeader( 'X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
				XHR.send( document.getElementById('rt-body').value );
			}
			button = document.getElementById('rt-button')
			button.addEventListener( 'click', function() {
				sendData();
			} )
		</script>
		<?php
	}
}

// Load our plugin within the WP admin dashboard.
if ( is_admin() ) {
	$plugin = new RestTestPlugin();
	$plugin->load();
}
