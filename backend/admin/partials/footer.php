    </main>
    <footer class="admin-footer">© <?= date('Y') ?> <?= e(setting('site_name', 'BB Caterers')) ?> · <?= e(setting('site_tagline')) ?></footer>
  </div>
</div>

<!-- Confirm dialog -->
<div class="modal" id="confirmModal" hidden>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="modal-icon danger"><i data-lucide="trash-2"></i></div>
    <h3 id="confirmTitle">Are you sure?</h3>
    <p id="confirmText">This cannot be undone.</p>
    <div class="modal-actions">
      <button type="button" class="btn ghost" data-confirm-cancel>Cancel</button>
      <button type="button" class="btn danger" data-confirm-ok>Yes, delete</button>
    </div>
  </div>
</div>

<div class="toasts" id="toasts" aria-live="polite"></div>
<script>window.__FLASH__ = <?= json_encode(take_flashes(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="assets/admin.js?v=3"></script>
</body>
</html>
