    </div>
</div>
</div>
<footer class="bg-dark text-white text-center py-2 mt-4">
    <small>WRQTestMall 管理后台 &copy; 2026 - 仅供安全学习</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extra_js)): ?>
<?php foreach ((array)$extra_js as $js): ?>
<script src="<?= base_url() ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>
