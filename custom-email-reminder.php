<?php
/*
Plugin Name: Custom Email Reminder
Description: Send an email to users 24 hours after registration.
Version: 1.1
*/

function customEmailReminderMenu() {
    add_menu_page(
        'Custom Email Reminder',
        'Email Reminder',
        'manage_options',
        'custom-email-reminder',
        'customEmailReminderPage',
        'dashicons-email-alt'
    );
}
add_action('admin_menu', 'customEmailReminderMenu');

function setEmailBody($user_email, $user_url){
  return "
    <table style='width: 500px; font-family: Open Sans, sans-serif;'>
	  <tbody>
		<tr style='display: block; border-bottom: 1px solid #e5e5e5;'>
		  <td style='padding: 10px; color: #555;'>
			<p>Hi there, we are still waiting for your onboarding form, and without it, we're not able to assign a team to your account.</p>
            
            <p>Please login on the website and fill it out. As soon as you complete it, we'll create your profile and match you with a team (up to 1 business day).</p>
			
			<br><br>
			<p style='font-family: Helvetica, Arial, sans-serif; font-size: 13px;line-height: 1.5em;'>For your first access use these credentials below:<br>
			username: $user_email <br>
			password: change_123
			</p>
			<br><br>

            <a rel='noopener' target='_blank' href='$user_url' style='background-color: #43b5a0; font-size: 15px; font-family: Helvetica, Arial, sans-serif; font-weight: bold; text-decoration: none; padding: 10px 20px; color: #ffffff; border-radius: 50px; display: inline-block; mso-padding-alt: 0;'>
                <!--[if mso]>
                <i style='letter-spacing: 25px; mso-font-width: -100%; mso-text-raise: 30pt;'>&nbsp;</i>
                <![endif]-->
                <span style='mso-text-raise: 15pt;'>Fill out onboarding form</span>
                <!--[if mso]>S
                <i style='letter-spacing: 25px; mso-font-width: -100%;'>&nbsp;</i>
                <![endif]-->
            </a>
            
            <p>If you have any questions, please let me know.</p>

            <p>Speak soon,<br>
            Wanessa - Client Success @ Deer Designer</p>
		  </td>
		</tr>
	  </tbody>
	  <tfoot style='text-align: left;'>
	    <th colspan='2' style='font-size: 12px; color: #43B5A0; font-weight: 100; margin-top: 30px; display: block;'>
            <p>If you already filled your onboarding form, ignore this email!</p>
            <a href='https://deerdesigner.com' target='_blank'>Deer Designer</a>
	    </th>
	  </tfoot>
    </table>
";
}


function customEmailReminderPage() {
   
    if (isset($_POST['submit'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';

        $test_user_email = $_POST['cer_test_email'];
        $test_subject = $_POST['cer_test_subject'];
        sendTestEmail($test_user_email, $test_subject);
    }

    ?>
    <div class="wrap">
        <h2>Custom Email Reminder Settings</h2>
        <form method="post">
            <?php settings_fields('custom-email-reminder-settings'); ?>
            <?php do_settings_sections('custom-email-reminder'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Email Address</th>
                    <td><input type="email" name="cer_test_email" value="" style="width: 50%" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Subject</th>
                    <td><input type="text" name="cer_test_subject" value="" style="width: 50%" /></td>
                </tr>
             
            </table>
            <?php submit_button('Send Test Email', 'primary', 'submit'); ?>
        </form>
    </div>
    <?php
}


function sendTestEmail($test_user_email, $test_subject) {
    $user_ur = "https://dash.deerdesigner.com/signup/onboarding/";
    $body = setEmailBody($test_user_email, $user_ur);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: Wanessa <wanessa.silva@deerdesigner.com>',
    );


    wp_mail($test_user_email, $test_subject, $body, $headers);

}



function sendEmailAfterRegistration($user_email, $user_url) {
    $send_time = time() + 3600; //ONE HOUR FROM NOW;
    $body = setEmailBody($user_email, $user_url);


    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: Wanessa <wanessa.silva@deerdesigner.com>',
    );

    $subject = 'Onboarding Form';

    wp_schedule_single_event($send_time, 'send_email_event', array($user_email, $subject, $body, $headers));
}
add_action('emailReminderHook', 'sendEmailAfterRegistration', 10, 2);



function sendScheduledEmail($user_email, $subject, $body, $headers) {
    $user = get_user_by('email', $user_email);
    $isUserOnboarded =  get_user_meta($user->ID, 'is_user_onboarded', true);

    if(!$isUserOnboarded){
        wp_mail($user_email, $subject, $body, $headers);
    }
}
add_action('send_email_event', 'sendScheduledEmail', 10, 4);