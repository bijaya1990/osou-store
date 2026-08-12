<?php
/**
 * Admin entry point — sends the visitor to the dashboard or the login screen.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

npr_redirect(npr_current_admin() ? npr_url('admin/dashboard.php') : npr_url('admin/login.php'));
