# cool-php-persian-captcha
Cool PHP library to create Persian CAPTCHAs

This project generates friendly captcha images. This project provides the SimpleCaptcha class.
Some fetures are: Background and foreground colors, dictionary words, non-dictionary random words, blur, shadows, JPEG and PNG support.


Basic example
-------------


```php
session_start();
$captcha = new SimpleCaptcha();
// Change configuration...
//$captcha->wordsFile = null;           // Disable dictionary words and use random letters instead
//$captcha->session_var = 'secretword'; // Changes the session variable from 'captcha' to 'secretword'
$captcha->CreateImage();
```

... will output an image, for example:
<br>
![https://raw.githubusercontent.com/nimah79/cool-php-persian-captcha/master/example.png](https://raw.githubusercontent.com/nimah79/cool-php-persian-captcha/master/example.png)



You can validate the php captcha with: (case-insensitive version)

```php
if(empty($_SESSION['captcha']) || strtolower(trim($_REQUEST['captcha'])) != $_SESSION['captcha']) {
    return "Invalid captcha";
}
```
