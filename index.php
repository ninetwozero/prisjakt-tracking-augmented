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
                $_SESSION['prisjakt_username'] = $username;
                $_SESSION['prisjakt_user_id'] = $sessionData['user_id'];
                $_SESSION['prisjakt_password_hash'] = $sessionData['password_hash'];
                session_write_close();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
            $out[] = 'Unable to login user <b> ' . $username . '</b>';
        }
    }

    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        unset($_SESSION['prisjakt_username']);
        unset($_SESSION['prisjakt_user_id']);
        unset($_SESSION['prisjakt_password_hash']);
        session_write_close();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Augmented Price Tracking on Prisjakt&trade;</title>

        <link href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css" />
        <link href="//cdn.muicss.com/mui-0.9.24/css/mui.min.css" rel="stylesheet" type="text/css" />
        <script src="//cdn.muicss.com/mui-0.9.24/js/mui.min.js"></script>
        <style>
            body {
                font-family: "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif;
                background: #f0f0f0;
            }

            .status-message {
                background: rgba(255, 64, 129, 0.25);
                padding: 8px;
                margin-bottom: 16px;
                margin-left: 16px;
                margin-right: 16px;
                margin: 0 auto;
            }

            .toolbar {
                height: 48px;
                line-height: 48px;
                background: #2196F3;
                padding: 0 16px;
                color: #fff;
            }

            .toolbar-actions {
                float: right;
                list-style-type: none;
                text-align: right;
            }

            .toolbar-actions li {
                padding: 0 8px;
            }

            .toolbar-actions li:hover {
                background: rgba(255, 255, 255, 0.2);
            }

            .username-link {
                color: #FFF;
                display: inline-block;
                text-decoration: none;
            }

            .username-link:hover {
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <span class="mui--text-title">Augmented Prisjakt</span>
            <?php if (hasApiCredentials()) { ?>
            <ul class="toolbar-actions">
                <li><a class="username-link" href="<?= $_SERVER['PHP_SELF'] ?>?action=logout">Sign out <strong><?= $_SESSION['prisjakt_username'] ?></strong></a></li>
            </ul>
            <?php } ?>
        </div>
        <?php if (count($out)) { ?>
            <div class="mui-container" style="margin-top: 16px">
                <div class="mui-container status-message">
                    <div class="mui--text-accent">
                        <?= implode('<br>', $out) ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="mui-container" style="margin-top: <?= count($out) ? 16 : 32 ?>px">
            <div class="mui-panel">
                <form class="mui-form" method="POST" action="<?= $_SERVER['PHP_SELF']; ?>">
                    <?php if (hasApiCredentials()) { ?>
                        <div class="mui-textfield">
                            <input name="prisjakt_search_url" type="url" placeholder="Ex. https://www.prisjakt.nu/#rparams=ss=NOCCO%20Focus" required>
                            <label>Prisjakt search URL</label>
                        </div>
                        <button name="submit" class="mui-btn mui-btn--accent mui-btn--raised" type="submit">Add products</button>
                    <?php } else { ?>
                        <div class="mui--text-title" style="margin-bottom: 8px">Sign in to Prisjakt</div>
                        <div class="mui-textfield mui-textfield--float-label">
                            <input name="prisjakt_username" required>
                            <label>Username</label>
                        </div>
                        <div class="mui-textfield mui-textfield--float-label">
                            <input name="prisjakt_password" type="password" required>
                            <label>Password</label>
                        </div>
                        <button name="submit" class="mui-btn mui-btn--accent mui-btn--raised" type="submit">Sign in</button>
                    <?php } ?>
                </form>
            </div>
        </div>
    </body>
</html>
