</main>
<footer class="site-footer" id="contact">
  <div class="container footer-grid">
    <div>
      <div class="brand">
        <img src="/assets/img/logo/unicef-color.png" alt="UNICEF" class="brand-logo">
        <span class="brand-text">UNICEF</span>
      </div>
      <p class="muted" data-i18n="footer.tagline"><?= t('footer.tagline') ?></p>
    </div>
    <div>
      <h4 data-i18n="contact.title"><?= t('contact.title') ?></h4>
      <p>
        <span data-i18n="contact.telegram"><?= t('contact.telegram') ?></span> :
        <a href="<?= e(CONTACT_TELEGRAM_LINK) ?>" target="_blank" rel="noopener">
          <?= e(CONTACT_TELEGRAM) ?>
        </a><br>
        <a href="<?= e(CONTACT_TELEGRAM_LINK) ?>" target="_blank" rel="noopener">t.me/donorphelinat</a><br>
        <span data-i18n="contact.email"><?= t('contact.email') ?></span> :
        <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a>
      </p>
    </div>
    <div>
      <h4 data-i18n="share.title"><?= t('share.title') ?></h4>
      <div class="share-row">
        <?php $url = urlencode(SITE_URL); $msg = urlencode("Aidez-moi à sauver des enfants de la famine. Chaque don compte 🙏 " . SITE_URL); ?>
        <a class="share-btn whatsapp" target="_blank" rel="noopener" href="https://wa.me/?text=<?= $msg ?>" aria-label="WhatsApp">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.52 3.48A11.78 11.78 0 0 0 12.06 0C5.5 0 .15 5.34.15 11.9c0 2.1.55 4.14 1.6 5.94L0 24l6.32-1.66a11.86 11.86 0 0 0 5.74 1.46h.01c6.56 0 11.9-5.34 11.9-11.9 0-3.18-1.24-6.17-3.45-8.42zM12.07 21.6h-.01a9.7 9.7 0 0 1-4.95-1.36l-.36-.21-3.75.98 1-3.65-.23-.37a9.7 9.7 0 0 1-1.49-5.18c0-5.37 4.37-9.74 9.74-9.74 2.6 0 5.04 1.01 6.88 2.85a9.66 9.66 0 0 1 2.85 6.89c0 5.37-4.37 9.74-9.68 9.74zm5.6-7.3c-.31-.16-1.83-.9-2.11-1-.28-.11-.49-.16-.7.16-.2.31-.79 1-.97 1.2-.18.21-.36.23-.67.08-.31-.16-1.31-.48-2.5-1.54-.92-.82-1.55-1.84-1.73-2.15-.18-.31-.02-.48.14-.63.14-.14.31-.36.47-.55.16-.18.21-.31.31-.52.1-.21.05-.39-.03-.55-.08-.16-.7-1.69-.96-2.31-.25-.6-.51-.52-.7-.53l-.6-.01c-.21 0-.55.08-.84.39-.28.31-1.09 1.07-1.09 2.6s1.12 3.02 1.27 3.23c.16.21 2.2 3.36 5.32 4.71.74.32 1.32.51 1.78.66.75.24 1.43.21 1.97.13.6-.09 1.83-.75 2.09-1.47.26-.72.26-1.34.18-1.47-.07-.12-.28-.2-.59-.36z"/></svg>
          <span>WhatsApp</span>
        </a>
        <a class="share-btn facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>" aria-label="Facebook">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.51 1.49-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.45 2.89h-2.33v6.99A10 10 0 0 0 22 12z"/></svg>
          <span>Facebook</span>
        </a>
        <a class="share-btn telegram" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= $url ?>&text=<?= $msg ?>" aria-label="Telegram">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.24 3.64 11.95c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71l-4.14-3.06-2 1.94c-.23.23-.42.42-.86.42z"/></svg>
          <span>Telegram</span>
        </a>
        <a class="share-btn tiktok" target="_blank" rel="noopener" href="https://www.tiktok.com/" aria-label="TikTok">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V8.85a8.16 8.16 0 0 0 4.77 1.52V6.92a4.85 4.85 0 0 1-1.84-.23z"/></svg>
          <span>TikTok</span>
        </a>
      </div>
    </div>
  </div>
  <div class="container footer-bottom">
    <small>© <?= date('Y') ?> UNICEF — <span data-i18n="footer.rights"><?= t('footer.rights') ?></span></small>
  </div>
</footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
