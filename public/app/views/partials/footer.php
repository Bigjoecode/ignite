<footer class="ig-foot">
  <div class="ig-foot__wrap">
    <div class="ig-foot__grid">

      <div class="ig-foot__brand">
        <a href="/" aria-label="Ignite Orthodontics home">
          <img src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics" loading="lazy" decoding="async">
        </a>
        <p>Braces and clear aligners for kids, teens and adults across <?= count(locations()) ?> Michigan offices.</p>
        <a class="ig-foot__cta" href="<?= e(booking_url()) ?>">Request a Consultation</a>
        <ul class="ig-foot__social">
          <li><a href="<?= e(cfg('social')['Facebook']) ?>" aria-label="Ignite Orthodontics on Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-7.3h2.5l.4-2.9h-2.9V8.9c0-.8.2-1.4 1.4-1.4h1.6V4.9c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.2v2.4H7.6v2.9h2.5V21h3.4z"/></svg></a></li>
          <li><a href="<?= e(cfg('social')['Instagram']) ?>" aria-label="Ignite Orthodontics on Instagram" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7.4A4.6 4.6 0 1 0 12 16.6 4.6 4.6 0 0 0 12 7.4zm0 7.6a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm5.8-7.8a1.1 1.1 0 1 1-2.2 0 1.1 1.1 0 0 1 2.2 0zM12 4.6c2.1 0 2.4 0 3.2.04 1.9.09 2.8 1 2.9 2.9.04.8.04 1.1.04 3.2s0 2.4-.04 3.2c-.09 1.9-1 2.8-2.9 2.9-.8.04-1.1.04-3.2.04s-2.4 0-3.2-.04c-1.9-.09-2.8-1-2.9-2.9C5.86 14.4 5.86 14.1 5.86 12s0-2.4.04-3.2c.09-1.9 1-2.8 2.9-2.9C9.6 4.6 9.9 4.6 12 4.6zM12 3c-2.2 0-2.5 0-3.3.05-2.7.12-4.1 1.6-4.3 4.3C4.3 8.2 4.3 8.5 4.3 12s0 3.8.05 4.6c.12 2.7 1.6 4.1 4.3 4.3.8.05 1.1.05 3.3.05s2.5 0 3.3-.05c2.7-.12 4.1-1.6 4.3-4.3.05-.8.05-1.1.05-4.6s0-3.8-.05-4.6c-.12-2.7-1.6-4.1-4.3-4.3C14.5 3 14.2 3 12 3z"/></svg></a></li>
        </ul>
      </div>

      <nav aria-label="Treatments">
        <h3>Treatments</h3>
        <ul>
<?php foreach (service_menu() as [$href, $label]): ?>
          <li><a href="<?= e($href) ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
        </ul>
      </nav>

      <nav aria-label="Patients">
        <h3>Patients</h3>
        <ul>
          <li><a href="/about-us/">About Us</a></li>
          <li><a href="/meet-the-doctor/">Meet the Doctor</a></li>
          <li><a href="/patient-info/">Patient Info</a></li>
          <li><a href="/insurance-financing/">Insurance &amp; Financing</a></li>
          <li><a href="/refer-a-patient/">Refer a Patient</a></li>
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/contact-us/">Contact Us</a></li>
          <li><a href="/sitemap/">Sitemap</a></li>
        </ul>
      </nav>

      <nav aria-label="Locations">
        <h3>Locations</h3>
        <ul>
<?php foreach (locations() as $l): ?>
          <li><a href="/locations/<?= e($l['slug']) ?>/"><?= e($l['name']) ?></a></li>
<?php endforeach; ?>
        </ul>
      </nav>

    </div>

    <div class="ig-foot__bar">
      <p>&copy; <?= date('Y') ?> Ignite Orthodontics. All rights reserved.</p>
      <p><a href="/privacy-policy/">Privacy Policy</a><span aria-hidden="true"> &middot; </span><a href="/terms-and-conditions/">Terms and Conditions</a></p>
    </div>
  </div>
</footer>
