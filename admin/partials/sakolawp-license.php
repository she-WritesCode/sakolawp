<?php
global $sakola_lbapi;
global $sakola_lb_verify_res;
$lb_activate_res = null;
$lb_deactivate_res = null;
if(!empty($_POST['client_name'])&&!empty($_POST['license_code'])) {
	check_admin_referer('lb_update_license', 'lb_update_license_sec');
	$lb_activate_res = $sakola_lbapi->activate_license(
		strip_tags(trim($_POST['license_code'])), 
		strip_tags(trim($_POST['client_name']))
	);
	$sakola_lb_verify_res = $sakola_lbapi->verify_license();
	$sakola_lb_verify_res_a = array(
		'status' => true,
	);
	update_option( 'sakola_lb_verify_res', $sakola_lb_verify_res_a );
}
if(!empty($_POST['lb_deactivate'])) {
	check_admin_referer('lb_deactivate_license', 'lb_deactivate_license_sec');
	$lb_deactivate_res = $sakola_lbapi->deactivate_license();
	$sakola_lb_verify_res = $sakola_lbapi->verify_license();
	$sakola_lb_verify_res_a = array(
		'status' => false,
	);
	update_option( 'sakola_lb_verify_res', $sakola_lb_verify_res_a );
}
$lb_update_data = $sakola_lbapi->check_update(); ?>

<div class="wrap">
	<h1><?php esc_html_e('SakolaWP License', 'sakolawp'); ?></h1>
	<?php if($sakola_lb_verify_res['status']){ ?> 
		<div class="notice notice-info">
			<p><?php esc_html_e('Activated! Your license is valid.', 'sakolawp'); ?></p>
		</div> 
	<?php }else{ ?> 
		<div class="notice notice-error">
			<p><?php echo (!empty($lb_activate_res['message']))?$lb_activate_res['message']:esc_html__('No license has been provided yet or the provided license is invalid.', 'sakolawp') ?></p>
		</div> 
	<?php }?>
	<form action="" method="post">
		<?php wp_nonce_field('lb_update_license', 'lb_update_license_sec'); ?>
		<table>   
			<tr>
				<th><?php esc_html_e('License code', 'sakolawp'); ?></th>
				<td>
					<input type="text" name="license_code" size="50" placeholder="<?php 
					if($sakola_lb_verify_res['status']){
						echo esc_attr__('Enter the license code here to update', 'sakolawp');
					}else{
						echo esc_attr__('Enter the license code here', 'sakolawp');
					} ?>" required>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Envato Username', 'sakolawp'); ?></th>
				<td>
					<input type="text" name="client_name" size="50" placeholder="<?php 
					if($sakola_lb_verify_res['status']){
						echo esc_html__('Enter your Envato username here to update', 'sakolawp');
					}else{
						echo esc_html__('Enter your Envato username here', 'sakolawp');
					} ?>" required>
				</td>
			</tr>    
			<tr>
				<td>
					<div style="padding-top: 10px;">
						<input type="submit" value="Activate" class="button button-primary">
					</div>
				</td>
			</tr>
		</table>

		<div style="margin-top: 10px;">
			<p><?php echo esc_html__('How to get purchase code?', 'sakolawp'); ?></p>
			<a class="btn btn-sm skwp-btn btn-success" href="<?php echo esc_url("https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"); ?>"><?php echo esc_html__('Click Here!', 'sakolawp'); ?></a>
		</div>
	</form>
	<?php if($sakola_lb_verify_res['status']){ ?>
		<h2 class="title" style="padding-top:10px;"><?php esc_html_e('Deactivate License', 'sakolawp'); ?></h2>
		<p style="max-width: 450px;">
			<?php esc_html_e('If you wish to use this license for activating plugin on a different server, you can first release your license from this server by deactivating it below.', 'sakolawp'); ?>
		</p>
		<?php if(empty($lb_deactivate_res)){ ?>
			<form action="" method="post">
				<?php wp_nonce_field('lb_deactivate_license', 'lb_deactivate_license_sec'); ?>
				<input type="hidden" name="lb_deactivate" value="yes">
				<input type="submit" value="Deactivate" class="button">
			</form>
		<?php } ?>
	<?php } ?>
	<?php if($sakola_lb_verify_res['status']){ ?>
		<h2 class="title" style="padding-top:10px;"><?php esc_html_e('Plugin Updates', 'sakolawp'); ?></h2>
		<p>
			<strong><?php echo esc_html($lb_update_data['message']); ?></strong>
		</p>
		<?php if($lb_update_data['status']){ ?>
			<p style="max-width: 700px;"><?php esc_html_e('Changelog: ', 'sakolawp'); ?>
				<?php echo strip_tags($lb_update_data['changelog'], '<ol><ul><li><i><b><strong><p><br><a><blockquote>'); ?>
			</p>
			<?php if(!empty($_POST['update_id'])){
				check_admin_referer('lb_update_download', 'lb_update_download_sec');
				$sakola_lbapi->download_update(
					strip_tags(trim($_POST['update_id'])), 
					strip_tags(trim($_POST['has_sql'])), 
					strip_tags(trim($_POST['version']))
				);
				if (false !== get_transient('licensebox_next_update_check')) {
					delete_transient('licensebox_next_update_check');
				}
			?>
			<?php }else{ ?>
				<form action="" method="POST">
					<?php wp_nonce_field('lb_update_download', 'lb_update_download_sec'); ?>
					<input type="hidden" value="<?php echo esc_attr($lb_update_data['update_id']); ?>" name="update_id">
					<input type="hidden" value="<?php echo esc_attr($lb_update_data['has_sql']); ?>" name="has_sql">
					<input type="hidden" value="<?php echo esc_attr($lb_update_data['version']); ?>" name="version">
					<div style="padding-top: 10px;">
						<input type="submit" value="Download and Install Update" class="button button-secondary">
					</div>
				</form>
			<?php } ?>
		<?php } ?>
	<?php } ?>
</div>