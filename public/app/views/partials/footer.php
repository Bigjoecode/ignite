<footer class="ig-foot">
  <div class="ig-foot__wrap">
    <div class="ig-foot__grid">

      <div class="ig-foot__brand">
        <a href="/" aria-label="Ignite Orthodontics home">
          <img src="/assets/img/ignitelogo.png" alt="Ignite Orthodontics" loading="lazy" decoding="async">
        </a>
        <p>Braces and clear aligners for kids, teens and adults across <?= count(locations()) ?> Michigan offices.</p>
        <a class="ig-foot__cta" href="/contact-us/">Request a Consultation</a>
        <a class="ig-foot__phone" href="tel:<?= e(cfg('phone_tel')) ?>"><?= e(cfg('phone')) ?></a>
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
          <li><a href="/patient-info/">Patient Info</a></li>
          <li><a href="/insurance-financing/">Insurance &amp; Financing</a></li>
          <li><a href="/refer-a-patient/">Refer a Patient</a></li>
          <li><a href="/blog/">Blog</a></li>
          <li><a href="/contact-us/">Contact Us</a></li>
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
