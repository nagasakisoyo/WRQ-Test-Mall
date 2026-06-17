</div>
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p class="mb-0">&copy; 2026 WRQTestMall - 仅供安全学习使用，禁止用于非法用途</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url() ?>/assets/js/utils.js"></script>
<?php if (isset($extra_js)): ?>
<?php foreach ((array)$extra_js as $js): ?>
<script src="<?= base_url() ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>
