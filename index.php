<?php
    require 'includes/init.php';

    $prisjakt = new Prisjakt([
        'user_id' => $_SESSION['prisjakt_user_id'] ?? '',
        'password_hash' => $_SESSION['prisjakt_password_hash'] ?? ''
    ]);

    $out = [];
    if (isset($_POST['submit'])) {
        if (hasApiCredentials()) {
            $searchUrl = $_POST['prisjakt_search_url'] ?? '';
            if ($searchUrl) {
                $products = $prisjakt->getProductsInSearch($searchUrl);
                foreach ($products as $product) {
                    $out[] = 'Adding alert for ' . $product->name . '...';
                    $prisjakt->addAlertForProduct($product->id);
                }
            } else {
                $out[] = 'No search URL provided';
            }
        } else {
            $username = $_POST['prisjakt_username'] ?? '';
            $password = $_POST['prisjakt_password'] ?? '';

            $sessionData = $prisjakt->login($username, $password);
            if (count($sessionData)) {
                $_SESSION['prisjakt_user_id'] = $sessionData['user_id'];
                $_SESSION['prisjakt_password_hash'] = $sessionData['password_hash'];
                session_write_close();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
            die('Unable to login user = ' . $username);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Augmented Price Tracking on Prisjakt&trade;</title>

        <link href="//cdn.muicss.com/mui-0.9.24/css/mui.min.css" rel="stylesheet" type="text/css" />
        <script src="//cdn.muicss.com/mui-0.9.24/js/mui.min.js"></script>
        <style>
            body {
                background: #03A9F4;
            }
        </style>
    </head>
    <body>
        <div class="mui-container">
            <?php if (count($out)) { ?>
                <div class="mui-panel" style="margin-top: 64px">
                    <strong style="display: block">Messages</strong>
                    <?= implode('<br>', $out) ?>
                </div>
            <?php } ?>
            <div class="mui-panel" style="margin-top: <?= count($out) ? 16 : 64 ?>px">
                <form class="mui-form" method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <?php if (hasApiCredentials()) { ?>
                        <div class="mui-textfield">
                            <input name="prisjakt_search_url" type="url" placeholder="Ex. https://www.prisjakt.nu/#rparams=ss=NOCCO%20Focus" required>
                            <label>Prisjakt search URL</label>
                        </div>
                        <button name="submit" class="mui-btn mui-btn--primary mui-btn--raised" type="submit">Add products</button>
                    <?php } else { ?>
                        <div class="mui-textfield mui-textfield--float-label">
                            <input name="prisjakt_username" required>
                            <label>Username</label>
                        </div>
                        <div class="mui-textfield mui-textfield--float-label">
                            <input name="prisjakt_password" type="password" required>
                            <label>Password</label>
                        </div>
                        <button name="submit" class="mui-btn mui-btn--primary mui-btn--raised" type="submit">Sign in to Prisjakt&trade;</button>
                    <?php } ?>
                </form>
            </div>
        </div>
    </body>
</html>
