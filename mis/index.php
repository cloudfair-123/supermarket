<?php require 'includes/bootstrap.php'; redirect(user() ? 'dashboard.php' : 'login.php');
