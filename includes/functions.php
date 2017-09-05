<?php
    function hasApiCredentials() {
        return (
            (isset($_SESSION['prisjakt_user_id']) && $_SESSION['prisjakt_user_id']) &&
            (isset($_SESSION['prisjakt_password_hash']) && $_SESSION['prisjakt_password_hash'])
        );
    }