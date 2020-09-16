<?php 

$email_template = <<<EOD
<div style="line-height: 1.5; max-width: 100%; width: 600px; margin: 0 auto; color: #000000;">
<div style="padding: 20px; background-color: #ffffff; border: solid 1px #ddd;">
<p>$hello,</p>
<h1>$greeting</h1>
<p>$message</p>
<p>$itinerary</p>
<p>$quote</p>
<p>$contact_whatsapp:</p>
<p style="font-size: 20px; text-align: center; font-weight: 900;"><a style="display: block; text-decoration: none; padding: 10px 15px; color: #ffffff; background-color: #25d366;" href="https://wa.me/$whatsapp">&iexcl;Whatsapp!</a></p>
<p>$contact_tel:</p>
 <p style="font-size: 20px; text-align: center; font-weight: 900;"><span style="display: block; text-decoration: none; padding: 10px 15px; color: #000000; background-color: #f7f7f7;" >$tel</span></p>
</div>
</div>
EOD;

?>