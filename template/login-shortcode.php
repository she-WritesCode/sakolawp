<?php

$logo_id        	= get_theme_mod('custom_logo');
$logo_image     	= wp_get_attachment_image_src($logo_id, 'full');

//  register get link
$page = skwp_get_page_by_title('Student Registration');
?>

<form id="sakolawp_login_form" class="sakolawp_user_form sakolawp_form" action="" method="post">
	<fieldset class="skwp-form-inner">
		<?php if (!empty($logo_image)) { ?>
			<div class="skwp-logo">
				<img src="<?php echo esc_url($logo_image[0]); ?>" alt="<?php esc_html_e('logo', 'sakolawp'); ?>" />
			</div>
		<?php } ?>
		<h4 class="sakolawp_header"><?php esc_html_e('Great to have back!', 'sakolawp'); ?></h4>
		<?php sakolawp_show_error_messages(); ?>
		<p>
			<input name="sakolawp_user_login" id="sakolawp_user_login" class="required swkp-usr-form" type="text" placeholder="<?php esc_html_e('Username', 'sakolawp'); ?>" />
		</p>
		<p>
			<input name="sakolawp_user_pass" id="sakolawp_user_pass" class="required swkp-usr-form" type="password" placeholder="<?php esc_html_e('Password', 'sakolawp'); ?>" />
		</p>
		<div class="login meta skwp-clearfix">
			<p class="forgetmenot float-left"><label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php esc_html_e('Keep me logged in', 'sakolawp'); ?></label></p>
			<!-- <a href="<?php echo esc_url(get_permalink($page->ID)); ?>" class="register float-right"><?php esc_html_e('Not a member? Create an account', 'sakolawp'); ?></a> -->
		</div>
		<p>
			<input id="skwp-login-btn" type="hidden" name="sakolawp_login_nonce" value="<?php echo wp_create_nonce('sakolawp-login-nonce'); ?>" />
			<input id="sakolawp_login_submit" type="submit" value="<?php echo esc_html__('Login', 'sakolawp'); ?>" />
		</p>
	</fieldset>
</form>

<?php
