<?php


class Mailer {

	function sendActivate($email,$username,$act) {

		$subject = "Welcome to ".SERVER_NAME;

		$message = "Hello ".$username."

Thank you for your registration.

----------------------------
Name: ".$username."
Activation code: ".$act."
----------------------------

Click the following link in order to activate your account:
".SERVER."activate.php?code=".$act."

Greetings,
Travian adminision";

		$headers = "From: ".ADMIN_EMAIL."\n";

		$to      = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
		if ($to === '') return false;
		$subject = mb_encode_mimeheader($subject, 'UTF-8');
		return mail($to, $subject, $message, $headers);
	}

	function sendInvite($email,$uid,$text) {

		$subject = "".SERVER_NAME." registration";

		$message = "Hello,

Try the new ".SERVER_NAME."!


Link: ".SERVER."anmelden.php?id=ref".$uid."

".$text."


Greetings,
Travian";

		$headers = "From: ".ADMIN_EMAIL."\n";

		$to      = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
		if ($to === '') return false;
		$subject = mb_encode_mimeheader($subject, 'UTF-8');
		return mail($to, $subject, $message, $headers);
	}

	function sendPassword($email,$uid,$username,$npw,$cpw) {

		$subject = "Password forgotten";

		$message = "Hello ".$username."

You have requested a new password for Travian.

----------------------------
Name: ".$username."
Password: ".$npw."
----------------------------

Please click this link to activate your new password. The old password then
becomes invalid:

http://{$_SERVER['HTTP_HOST']}/password.php?cpw={$cpw}&npw={$uid}

If you want to change your new password, you can enter a new one in your profile
on tab \"account\".

In case you did not request a new password you may ignore this email.

Travian
";

		$headers = "From: ".ADMIN_EMAIL."\n";

		$to      = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
		if ($to === '') return false;
		$subject = mb_encode_mimeheader($subject, 'UTF-8');
		return mail($to, $subject, $message, $headers);
	}

	function sendPasswordResetLink($email,$uid,$username,$token) {

		$subject = "Password reset request";

		$link = "http://{$_SERVER['HTTP_HOST']}/password.php?action=reset&uid={$uid}&token={$token}";
		$message = "Hello {$username}

You have requested to reset your password for ".SERVER_NAME.".

----------------------------
Name: {$username}
----------------------------

Click the following link to set a new password:
{$link}

If you did not request a password reset you may ignore this email.

Travian
";

		$headers = "From: ".ADMIN_EMAIL."\n";

		$to      = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
		if ($to === '') return false;
		$subject = mb_encode_mimeheader($subject, 'UTF-8');
		return mail($to, $subject, $message, $headers);
	}

};
$mailer = new Mailer;
?>
