<?php
/*
Plugin Name: CubePoints Authorize.net Module
Plugin URI: #
Description: CubePoints  Authorize.net is a point management system and user can buy points with authorize.net.
Version: 1.0
Author: Devendra Sharma
Author URI:#
*/
/**
 * Detect plugin. For use in Admin area only.
 */
if ( ! function_exists('is_plugin_inactive')) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}
if (is_plugin_inactive('cubepoints/cubepoints.php')) {
    //plugin is not activated
	add_action( 'admin_notices', 'cubepoints_admin_notice' );
}
function cubepoints_admin_notice() {
    ?>
    <div class="error">
        <p><?php _e( 'Install CubePoints Plugin <a href="https://wordpress.org/plugins/cubepoints/">Here</a>', 'my-text-domain' ); ?></p>
    </div>
    <?php
}
if ( is_plugin_active( 'cubepoints/cubepoints.php' ) ) {
  //plugin is activated
  
require_once WP_PLUGIN_DIR . '/cubepoints/cubepoints.php';

cp_module_register(__('Authorize.net Top-up', 'cp') , 'authtopupcp' , '1.0', 'Rohit Gupta', 'http://www.conveysproject.com', 'http://conveysproject.com' , __('Allow users to buy points using Authorize.net.', 'cp'), 1);


function cp_module_authtopupcp_install(){
		add_option('cp_module_authtopupcp_account', '');
		add_option('cp_module_authtopupcp_transaction', '');
		add_option('cp_module_authtopupcp_sandbox', false);
		add_option('cp_module_authtopupcp_currency', 'USD');
		add_option('cp_module_authtopupcp_item', '%npoints% '.get_bloginfo('name').' '.__('Points', 'cp'));
		add_option('cp_module_authtopupcp_cancel', get_bloginfo('url').'/?cp_module_authtopupcp_return=0');
		add_option('cp_module_authtopupcp_thankyou', get_bloginfo('url').'/?cp_module_authtopupcp_return=1');
		add_option('cp_module_authtopupcp_price', 0.05);
		add_option('cp_module_authtopupcp_min', 1);
		add_option('cp_module_authtopupcp_form',"<form method=\"post\">\n<input type=\"hidden\" name=\"cp_module_authtopupcp_pay\" value=\"1\" />\nNumber of points to purchase:<br />\n<input type=\"text\" name=\"points\" /><br />\n<input type=\"submit\" value=\"Buy!\" />\n</form>");
}
add_action('cp_module_authtopupcp_activate','cp_module_authtopupcp_install');

if(cp_module_activated('authtopupcp')){

function cp_module_authtopupcp_shortcode( $atts ){
	$r = get_option('cp_module_authtopupcp_form');
	$r = str_replace('%min%',get_option('cp_module_authtopupcp_min'),$r);
	return $r;
}
add_shortcode('cp_authtopupcp','cp_module_authtopupcp_shortcode');

/** PayPal top-up logs hook */
add_action('cp_logs_description','cp_module_authtopupcp_logs', 10, 4);
function cp_module_authtopupcp_logs($type,$uid,$points,$data){
	if($type!='authorize.net') { return; }
	$data = unserialize($data);
	echo '<span title="'.__('Paid by', 'cp').': '.$data['payer_email'].'">'.__('Authorize.Net Points Top-up', 'cp').' (ID: '.$data['txn_id'].')</span>';
}

function cp_module_authtopupcp_round_up($value, $precision = 0) { 
    $sign = (0 <= $value) ? +1 : -1; 
    $amt = explode('.', $value); 
    $precision = (int) $precision; 
    
    if (strlen($amt[1]) > $precision) { 
        $next = (int) substr($amt[1], $precision); 
        $amt[1] = (float) (('.'.substr($amt[1], 0, $precision)) * $sign); 
        
        if (0 != $next) { 
            if (+1 == $sign) { 
                $amt[1] = $amt[1] + (float) (('.'.str_repeat('0', $precision - 1).'1') * $sign); 
            } 
        } 
    } 
    else { 
        $amt[1] = (float) (('.'.$amt[1]) * $sign); 
    } 
    
    return $amt[0] + $amt[1]; 
} 

function cp_module_authtopupcp_add_admin_page(){
	add_submenu_page('cp_admin_manage', 'CubePoints - ' .__('Authorize.Net Top-up','cp'), __('Authorize.Net Top-up','cp'), 'manage_options', 'cp_modules_authtopupcp_admin', 'cp_modules_authtopupcp_admin');
}
add_action('cp_admin_pages','cp_module_authtopupcp_add_admin_page');

function cp_modules_authtopupcp_admin(){

// handles form submissions
if ($_POST['cp_module_authtopupcp_form_submit'] == 'Y') {

	update_option('cp_module_authtopupcp_account', trim($_POST['cp_module_authtopupcp_account']));
	update_option('cp_module_authtopupcp_transaction', trim($_POST['cp_module_authtopupcp_transaction']));
	update_option('cp_module_authtopupcp_sandbox', (bool)$_POST['cp_module_authtopupcp_sandbox']);
	update_option('cp_module_authtopupcp_currency', $_POST['cp_module_authtopupcp_currency']);
	update_option('cp_module_authtopupcp_item', trim($_POST['cp_module_authtopupcp_item']));
		if(trim($_POST['cp_module_authtopupcp_cancel'])==''){ $_POST['cp_module_authtopupcp_cancel'] = get_bloginfo('url').'/?cp_module_authtopupcp_return=0'; }
	update_option('cp_module_authtopupcp_cancel', trim($_POST['cp_module_authtopupcp_cancel']));
		if(trim($_POST['cp_module_authtopupcp_thankyou'])==''){ $_POST['cp_module_authtopupcp_thankyou'] = get_bloginfo('url').'/?cp_module_authtopupcp_return=1'; }
	update_option('cp_module_authtopupcp_thankyou', trim($_POST['cp_module_authtopupcp_thankyou']));   
	update_option('cp_module_authtopupcp_price', ((float)$_POST['cp_module_authtopupcp_price']<=0)?1:(float)$_POST['cp_module_authtopupcp_price']);
	update_option('cp_module_authtopupcp_min', ((int)$_POST['cp_module_authtopupcp_min']<=0)?1:(int)$_POST['cp_module_authtopupcp_min']);
	update_option('cp_module_authtopupcp_form', trim(stripslashes($_POST['cp_module_authtopupcp_form'])));
	


	echo '<div class="updated"><p><strong>'.__('Settings Updated','cp').'</strong></p></div>';
}

function cp_module_authtopupcp_currSel($curr){
	if($curr == get_option('cp_module_authtopupcp_currency')) { echo 'selected'; }
}
if(get_option('cp_module_authtopupcp_sandbox')){
	$cp_module_authtopupcp_sandbox_checked = 'checked';
}
	
?>
<script type="text/javascript">
string1 = '<form method="post">'+"\n"+'<input type="hidden" name="cp_module_authtopupcp_pay" value="1" />'+"\n"+'Number of points to purchase:<br />'+"\n"+'<input type="text" name="points" /><br />'+"\n"+'<input type="submit" value="Buy!" />'+"\n"+'</form>';
string2 = '<form method="post">'+"\n"+'<input type="hidden" name="cp_module_authtopupcp_pay" value="1" />'+"\n"+'Number of points to purchase:<br />'+"\n"+'<select name="points">'+"\n"+'<option value="100">100 Points</option>'+"\n"+'<option value="200">200 Points</option>'+"\n"+'<option value="300">300 Points</option>'+"\n"+'<option value="400">400 Points</option>'+"\n"+'<option value="500">500 Points</option>'+"\n"+'</select>'+"\n"+'<br />'+"\n"+'<input type="submit" value="Buy!" />'+"\n"+'</form>';
string3 = '<form method="post">'+"\n"+'<input type="hidden" name="cp_module_authtopupcp_pay" value="1" />'+"\n"+'<input type="hidden" name="points" value="100" />'+"\n"+'<input type="submit" value="Buy 100 Points" />'+"\n"+'</form>';
string4 = '<a href="<?php bloginfo('url'); ?>/?cp_module_authtopupcp_pay=1&points=100">Buy 100 Points</a>';
</script>
<div class="wrap">
	<h2>CubePoints - <?php _e('Authorize.Net Top-up', 'cp'); ?></h2>
	<?php _e('Configure the Authorize.Net Top-up module.', 'cp'); ?><br /><br />

	<form name="cp_module_authtopupcp_form" method="post">
		<input type="hidden" name="cp_module_authtopupcp_form_submit" value="Y" />

	<h3><?php _e('Authorize.Net Settings','cp'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_account"><?php _e('Authorize.Net API Login ID', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_account" name="cp_module_authtopupcp_account" value="<?php echo get_option('cp_module_authtopupcp_account'); ?>" size="40" /></td>
		</tr>
        <tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_transaction"><?php _e('Authorize.Net Transaction Key', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_transaction" name="cp_module_authtopupcp_transaction" value="<?php echo get_option('cp_module_authtopupcp_transaction'); ?>" size="40" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_sandbox"><?php _e('Sandbox mode', 'cp'); ?>:</label></th>
			<td valign="middle"><input id="cp_module_authtopupcp_sandbox" name="cp_module_authtopupcp_sandbox" type="checkbox" value="1" <?php echo $cp_module_authtopupcp_sandbox_checked; ?> /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_currency"><?php _e('Currency', 'cp'); ?>:</label></th>
			<td valign="middle">
			<select id="cp_module_authtopupcp_currency" name="cp_module_authtopupcp_currency" class="widefat" width="230" style="width:270px;">
				<option value="AUD" <?php cp_module_authtopupcp_currSel('AUD'); ?>>Australian Dollars</option>
				<option value="CAD" <?php cp_module_authtopupcp_currSel('CAD'); ?>>Canadian Dollars</option>
				<option value="EUR" <?php cp_module_authtopupcp_currSel('EUR'); ?>>Euros</option>
				<option value="GBP" <?php cp_module_authtopupcp_currSel('GBP'); ?>>Pounds Sterling</option>
				<option value="JPY" <?php cp_module_authtopupcp_currSel('JPY'); ?>>Yen</option>
				<option value="USD" <?php cp_module_authtopupcp_currSel('USD'); ?>>U.S. Dollars</option>
				<option value="NZD" <?php cp_module_authtopupcp_currSel('NZD'); ?>>New Zealand Dollar</option>
				<option value="CHF" <?php cp_module_authtopupcp_currSel('CHF'); ?>>Swiss Franc</option>
				<option value="HKD" <?php cp_module_authtopupcp_currSel('HKD'); ?>>Hong Kong Dollar</option>
				<option value="SGD" <?php cp_module_authtopupcp_currSel('SGD'); ?>>Singapore Dollar</option>
				<option value="SEK" <?php cp_module_authtopupcp_currSel('SEK'); ?>>Swedish Krona</option>
				<option value="DKK" <?php cp_module_authtopupcp_currSel('DKK'); ?>>Danish Krone</option>
				<option value="PLN" <?php cp_module_authtopupcp_currSel('PLN'); ?>>Polish Zloty</option>
				<option value="NOK" <?php cp_module_authtopupcp_currSel('NOK'); ?>>Norwegian Krone</option>
				<option value="HUF" <?php cp_module_authtopupcp_currSel('HUF'); ?>>Hungarian Forint</option>
				<option value="CZK" <?php cp_module_authtopupcp_currSel('CZK'); ?>>Czech Koruna</option>
				<option value="ILS" <?php cp_module_authtopupcp_currSel('ILS'); ?>>Israeli Shekel</option>
				<option value="MXN" <?php cp_module_authtopupcp_currSel('MXN'); ?>>Mexican Peso</option>
				<option value="BRL" <?php cp_module_authtopupcp_currSel('BRL'); ?>>Brazilian Real</option>
				<option value="MYR" <?php cp_module_authtopupcp_currSel('MYR'); ?>>Malaysian Ringgits</option>
				<option value="PHP" <?php cp_module_authtopupcp_currSel('PHP'); ?>>Philippine Pesos</option>
				<option value="TWD" <?php cp_module_authtopupcp_currSel('TWD'); ?>>Taiwan New Dollars</option>
				<option value="THB" <?php cp_module_authtopupcp_currSel('THB'); ?>>Thai Baht</option>
			</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_item"><?php _e('Authorize.net item name', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_item" name="cp_module_authtopupcp_item" value="<?php echo get_option('cp_module_authtopupcp_item'); ?>" size="40" /> <br /><small>Shortcode: %points%, %npoints%</small></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_cancel"><?php _e('Cancel URL', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_cancel" name="cp_module_authtopupcp_cancel" value="<?php echo get_option('cp_module_authtopupcp_cancel'); ?>" size="40" /> <br /><small>URL to direct your users when they cancel the payment.</small></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_thankyou"><?php _e('Thank You URL', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_thankyou" name="cp_module_authtopupcp_thankyou" value="<?php echo get_option('cp_module_authtopupcp_thankyou'); ?>" size="40" /> <br /><small>URL to direct your users when they complete the payment.</small></td>
		</tr>
	</table>
	<br />
	<h3><?php _e('Points Settings','cp'); ?></h3>
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_price"><?php _e('Price per point', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_price" name="cp_module_authtopupcp_price" value="<?php echo get_option('cp_module_authtopupcp_price'); ?>" size="40" /> <br /><small>Entering 0.05 would mean that $1 buys you 20 points.</small></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="cp_module_authtopupcp_min"><?php _e('Minimum points per purchase', 'cp'); ?>:</label></th>
			<td valign="middle"><input type="text" id="cp_module_authtopupcp_min" name="cp_module_authtopupcp_min" value="<?php echo get_option('cp_module_authtopupcp_min'); ?>" size="40" /></td>
		</tr>
	</table>
	<br />
	<h3><?php _e('Form Settings','cp'); ?></h3>
	<label for="cp_module_authtopupcp_form"><?php _e('Purchase Form HTML Code', 'cp'); ?>:</label><br />
	<textarea id="cp_module_authtopupcp_form" name="cp_module_authtopupcp_form" cols="90" rows="13" style="font-size:10px;" /><?php echo get_option('cp_module_authtopupcp_form'); ?></textarea>
	<br />
	<small><?php _e('Choose a preset', 'cp'); ?>: 
		<a href="#" onClick="document.getElementById('cp_module_authtopupcp_form').value=string1;return false;"><?php _e('Enter any amount', 'cp'); ?></a> |
		<a href="#" onClick="document.getElementById('cp_module_authtopupcp_form').value=string2;return false;"><?php _e('Select from a list', 'cp'); ?></a> |
		<a href="#" onClick="document.getElementById('cp_module_authtopupcp_form').value=string3;return false;"><?php _e('Single button with fixed points', 'cp'); ?></a> |
		<a href="#" onClick="document.getElementById('cp_module_authtopupcp_form').value=string4;return false;"><?php _e('Link', 'cp'); ?></a>
	</small>
	<p>To insert the points purchase form into a page, use the following shortcode: <i>[cp_authtopupcp]</i></p>
	<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Options','cp'); ?>" />
	</p>
</form>
</div>
<?php
}

function cp_module_authtopupcp_pay(){
	if(isset($_REQUEST['cp_module_authtopupcp_pay']) && $_REQUEST['cp_module_authtopupcp_pay']!=''){
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		
	if(get_option('cp_module_authtopupcp_sandbox')){
		$loc = 'https://test.authorize.net/gateway/transact.dll';
	}
	else{
		$loc = 'https://secure.authorize.net/gateway/transact.dll';
	}
	$loginID		= get_option('cp_module_authtopupcp_account');
    $transactionKey = get_option('cp_module_authtopupcp_transaction');
	$countrycode = get_option('cp_module_authtopupcp_currency');
	$points = (int) $_REQUEST['points'];
	if(!is_user_logged_in()){
		cp_module_authtopupcp_showMessage(__('You must be logged in to purchase points!', 'cp'));
	}
	if($points<get_option('cp_module_authtopupcp_min')){
		cp_module_authtopupcp_showMessage(__('You must purchase a minimum of', 'cp').' '.get_option('cp_module_authtopupcp_min').' points!');
	}
	$price =  cp_module_authtopupcp_round_up(get_option('cp_module_authtopupcp_price') * $points, 2);
// an invoice is generated using the date and time
$invoice	= date('YmdHis');
// a sequence number is randomly generated
$sequence	= rand(1, 1000);
// a timestamp is generated
$timeStamp	= time();

// The following lines generate the SIM fingerprint.  PHP versions 5.1.2 and
// newer have the necessary hmac function built in.  For older versions, it
// will try to use the mhash library.
if( phpversion() >= '5.1.2' )
	{ $fingerprint = hash_hmac("md5", $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $price . "^".$countrycode, $transactionKey); }
else 
	{ $fingerprint = bin2hex(mhash(MHASH_MD5, $loginID . "^" . $sequence . "^" . $timeStamp . "^" . $price . "^".$countrycode, $transactionKey)); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<title><?php _e('Processing payment...', 'cp'); ?></title> 
	<meta name="robots" content="noindex, nofollow" /> 
	<link rel='stylesheet' id='thickbox-css'  href='<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)). 'style.css' ?>' type='text/css' media='all' /> 
</head>
<body>
	<form action="<?php echo $loc; ?>" method="post" name="authtopupcp_form">
    
    	<input type='hidden' name='x_login' value='<?php echo $loginID; ?>' />
        <input type='hidden' name='x_description' value='<?php echo 'User : ';$user=get_userdata(cp_currentUser()); echo $user->user_login;; ?>' />
        <input type='hidden' name='x_line_item' value='<?php echo "1<|>".str_replace('%points%',cp_formatPoints($points),str_replace('%npoints%',$points,get_option('cp_module_authtopupcp_item')))."<|>".str_replace('%points%',cp_formatPoints($points),str_replace('%npoints%',$points,get_option('cp_module_authtopupcp_item')))."<|>1<|>".$price."<|>N"; ?>' />
    <input type='hidden' name='x_currency_code' value='<?php echo get_option('cp_module_authtopupcp_currency'); ?>' />
	<input type='hidden' name='x_amount' value='<?php echo $price; ?>' />
	<input type='hidden' name='x_invoice_num' value='<?php echo $invoice; ?>' />
	<input type='hidden' name='x_fp_sequence' value='<?php echo $sequence; ?>' />
	<input type='hidden' name='x_fp_timestamp' value='<?php echo $timeStamp; ?>' />
	<input type='hidden' name='x_fp_hash' value='<?php echo $fingerprint; ?>' />
    <input type="hidden" name="x_cancel_url " value="<?php echo get_option('cp_module_authtopupcp_cancel'); ?>">
    <input type="hidden" name="x_receipt_link_url" value="<?php echo get_option('cp_module_authtopupcp_thankyou'); ?>">
    <input type="hidden" name="x_receipt_link_text" value="<?php _e('Return to', 'cp'); echo ' '; bloginfo('name'); ?>">
    <input type="hidden" name="x_receipt_link_method" value="POST">

	<input type='hidden' name='x_show_form' value='PAYMENT_FORM' />
	</form>
    
	<div id="container">
	<p id="load"><img src="<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)). 'load.gif' ?>" alt="<?php _e('Processing payment...', 'cp'); ?>" /></p>
	<p id="text">Processing payment...</p> 
	<p><a href="#" onClick="document.paypal_form.submit();return false;">Click here to continue if you are not automatically redirected &raquo;</a></p> 
	</div> 
	<script type="text/javascript">
		setTimeout("document.authtopupcp_form.submit()",2000);
	</script>
</body> 
</html> 
<?php
exit;
}
}

add_action('init','cp_module_authtopupcp_pay');
	
function cp_module_authtopupcp_message(){
		if(isset($_REQUEST['cp_module_authtopupcp_return']) && $_REQUEST['cp_module_authtopupcp_return']!=''){
		if($_REQUEST['cp_module_authtopupcp_return']=='1'){
			cp_module_authtopupcp_showMessage(__('Thank you for your purchase!', 'cp'));
		}
		if($_REQUEST['cp_module_authtopupcp_return']=='0'){
			cp_module_authtopupcp_showMessage(__('Your payment did not go through successfully!', 'cp'));
		}
		exit;
	}
}
	
function cp_module_authtopupcp_showMessage($message){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"> 
<head> 
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<title><?php bloginfo('name'); ?></title> 
	<meta name="robots" content="noindex, nofollow" /> 
	<link rel='stylesheet' id='thickbox-css'  href='<?php echo WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__)). 'style.css' ?>' type='text/css' media='all' /> 
</head>
<body>
	<div id="container">
	<p id="text"><?php echo $message; ?></p> 
	<p><a href="<?php bloginfo('url'); ?>">Click here to return to <?php bloginfo('name'); ?> &raquo;</a></p> 
	</div>
</body> 
</html> 
<?php
//print_r($_POST);

if(isset($_GET['cp_module_authtopupcp_return']) && $_GET['cp_module_authtopupcp_return']==1){
		// assign posted variables to local variables
		if($_POST['x_response_reason_text']=='This transaction has been approved.'){
			// process payment
			cp_points('authorize.net', cp_currentUser(), (int)$_POST['x_amount'], serialize(array('txn_id'=>$_POST['x_trans_id'],'payer_email'=>$payer_email,'amt'=>$_POST['x_amount'])));
			
			}}
exit();
}

add_action('init','cp_module_authtopupcp_message');
}

}


?>