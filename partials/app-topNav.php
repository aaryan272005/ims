<?php require_once __DIR__ . '/security.php'; ?>

<div class="dashboard_topNav">

    <script>
        window.appConfig = window.appConfig || {};
        window.appConfig.csrfToken = "<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>";
    </script>

    <a href="#" id="toggleBtn">
        <i class="fa fa-bars"></i>
    </a>

    <form action="database/logout.php" method="POST" style="margin:0;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit" class="logoutBtn" style="border:none;">
            <i class="fa fa-power-off"></i> Log-out
        </button>
    </form>

</div>
