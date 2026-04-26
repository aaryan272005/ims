<?php if (isset($_SESSION['response'])): ?>
    <?php $response = $_SESSION['response']; ?>
    <div class="responseMessage">
        <p class="<?= !empty($response['success']) ? 'successMessage' : 'errorMessage' ?>">
            <?= htmlspecialchars((string) ($response['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
    <?php unset($_SESSION['response']); ?>
<?php endif; ?>
