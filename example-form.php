<?php
/**
 * Sample script for Persian CAPTCHA generation and validation.
 *
 * @author  Jose Rodriguez <josecl@gmail.com>
 * @author  Nima HeydariNasab (added Persian support)
 * @license GPLv3
 *
 * @version 0.5
 *
 *
 * This is an example file with a simplified implementation
 * of the captcha for demonstration purposes.
 */
session_start();

?>
<!DOCTYPE html>
<html lang="fa">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="Author" content="Jose Rodriguez" />
<title>تست کپچای فارسی</title>
<style type="text/css">
body { font-family: Arial, sans-serif; padding: 20px; }
#result { border: 1px solid green; width: 300px; margin: 0 0 35px 0; padding: 10px 20px; font-weight: bold; }
</style>
</head>
<body dir="rtl" onload="document.getElementById('captcha-form').focus()">


<?php

/** Validate captcha */
if (!empty($_REQUEST['captcha'])) {
    if (empty($_SESSION['captcha']) || trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha']) {
        $captcha_message = 'متن واردشده اشتباه بود!';
        $style = 'background-color: #FF606C';
    } else {
        $captcha_message = 'متن واردشده صحیح بود!';
        $style = 'background-color: #CCFF99';
    }

    $request_captcha = htmlspecialchars($_REQUEST['captcha']);

    echo <<<HTML
        <div id="result" style="$style">
        <h2>$captcha_message</h2>
        <table>
        <tr>
            <td>کپچای سشن:</td>
            <td>{$_SESSION['captcha']}</td>
        </tr>
        <tr>
            <td>کپچای فرم:</td>
            <td>$request_captcha</td>
        </tr>
        </table>
        </div>
HTML;
    unset($_SESSION['captcha']);
}

?>




<p><strong>کلمه‌ی زیر را وارد کنید:</strong></p>

<form method="POST">
<img src="captcha.php" id="captcha">
<br>

<!-- CHANGE TEXT LINK -->
<a href="#" onclick="
    document.getElementById('captcha').src='captcha.php?'+Math.random();
    document.getElementById('captcha-form').focus();"
    id="change-image">خوانا نیست؟ ایجاد کد جدید</a><br/><br/>


<input type="text" name="captcha" id="captcha-form" autocomplete="off"><br/>
<input type="submit" value="ارسال">

</form>


</body>
</html>
