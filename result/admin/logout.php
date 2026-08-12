<?php
/**
 * Sign out. Uses POST-or-GET but always regenerates the session.
 */
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once NPR_BASE_PATH . '/includes/auth.php';

npr_logout();
npr_redirect(npr_url('admin/login.php'));
