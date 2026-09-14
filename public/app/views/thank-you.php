<div class="ig-home">

  <section class="pg-hero">
    <div class="ig-wrap">
      <span class="ty-badge" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg></span>
      <span class="ig-kicker">Request Received</span>
      <h1>Thank you<span data-ty-name></span>. <em>We&rsquo;ll be in touch soon.</em></h1>
      <p class="pg-hero__lead">Your consultation request has been sent to our team. We&rsquo;ll call or text you to confirm your appointment time.</p>
    </div>
  </section>

  <section class="ig-sec">
    <div class="ig-wrap ty-grid">

      <div class="ty-card ty-summary" data-ty-summary hidden>
        <h2>Your request</h2>
        <dl>
          <div><dt>Treatment</dt><dd data-ty="treatment">&mdash;</dd></div>
          <div><dt>Office</dt><dd data-ty="office">&mdash;</dd></div>
          <div><dt>Preferred time</dt><dd data-ty="when">&mdash;</dd></div>
        </dl>
      </div>

      <div class="ty-card">
        <h2>What happens next</h2>
        <ol class="ty-steps">
          <li><b>We review your request</b><span>Our team checks availability at the office you chose.</span></li>
          <li><b>We confirm your time</b><span>Expect a call or text from Ignite Orthodontics to set your appointment.</span></li>
          <li><b>Your no-cost consultation</b><span>Meet the team, get an exam and talk through your treatment options.</span></li>
        </ol>
      </div>

      <div class="ty-card ty-help">
        <h2>Need to make a change?</h2>
        <p>Call us and we&rsquo;ll update your request.</p>
        <a class="ig-btn ig-btn--primary" data-ty-call href="tel:<?= e(cfg('phone_tel')) ?>">Call <?= e(cfg('phone')) ?></a>
        <div class="ty-links">
          <a href="/">Back to Home</a>
          <a href="/treatments/">Explore Treatments</a>
        </div>
      </div>

    </div>
  </section>
</div>

<script>
(function () {
  var data = null;
  try { data = JSON.parse(sessionStorage.getItem('igBooking') || 'null'); } catch (e) { data = null; }
  if (!data) return;
  var name = document.querySelector('[data-ty-name]');
  if (name && data.first) name.textContent = ', ' + data.first;
  ['treatment', 'office', 'when'].forEach(function (key) {
    var el = document.querySelector('[data-ty="' + key + '"]');
    if (el && data[key]) el.textContent = data[key];
  });
  if (data.treatment || data.office) document.querySelector('[data-ty-summary]').hidden = false;
  var call = document.querySelector('[data-ty-call]');
  if (call && data.tel && data.phone) {
    call.href = 'tel:' + data.tel;
    call.textContent = 'Call ' + data.phone;
  }
})();
</script>
