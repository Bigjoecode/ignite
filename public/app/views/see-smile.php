<?php
// See Your Smile (/see-your-smile/): send a photo, see what a straighter smile
// could look like, and get a real opinion from the team.
// Posts to /see-smile (app/see-smile.php). Vars: $office (may be null), $aiOn.
$check = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5.5 12.5l4 4L18.5 7.5"/></svg>';
$phone = $office['phone'] ?? cfg('phone');
$tel   = $office['tel'] ?? cfg('phone_tel');
?>
<div class="bk sm">

  <section class="bk-hero">
    <div class="bk-wrap">
      <span class="bk-kicker">See Your Smile</span>
      <h1>See What a <em>Straighter Smile</em> Could Look Like</h1>
      <p>Send us a photo of your smile. You will see what treatment could change, and one of our orthodontic team will look at it properly and tell you what would actually be involved&mdash;at no cost.</p>
    </div>
  </section>

  <div class="bk-wrap bk-grid">
    <div class="bk-card" data-sm-root>
      <form class="ig-bk__body" action="/see-smile" method="post" enctype="multipart/form-data" novalidate data-sm-form>
        <input type="text" name="website" value="" class="ig-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="source" value="/see-your-smile/">
        <input type="hidden" name="draft_id" value="" data-sm-draft>

        <h2 class="ig-bk__q">Your photo</h2>
        <p class="ig-bk__sub">A straight-on photo of you smiling, with your teeth showing. Daylight helps. Nobody but our team sees it.</p>

        <label class="sm-drop" data-sm-drop>
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required data-sm-file>
          <span class="sm-drop__inner" data-sm-empty>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
            <b>Choose a photo</b>
            <small>JPG or PNG, up to 12MB</small>
          </span>
          <img class="sm-drop__preview" alt="" hidden data-sm-preview>
        </label>
        <p class="sm-note" data-sm-chosen hidden>Looks good. <button type="button" class="sm-note__change" data-sm-clear>Choose a different photo</button></p>

        <h2 class="ig-bk__q">What bothers you most?</h2>
        <div class="sm-concerns">
<?php foreach (smile_concerns() as $key => $label): ?>
          <label class="sm-concern">
            <input type="radio" name="concerns" value="<?= e($key) ?>"<?= $key === 'unsure' ? ' checked' : '' ?>>
            <span><?= e($label) ?></span>
          </label>
<?php endforeach; ?>
        </div>

        <h2 class="ig-bk__q">Where to send it</h2>
        <div class="ig-bk__grid">
          <label class="ig-bk__field"><span>First name <i aria-hidden="true">*</i></span><input type="text" name="first_name" autocomplete="given-name" maxlength="60" required></label>
          <label class="ig-bk__field"><span>Last name</span><input type="text" name="last_name" autocomplete="family-name" maxlength="60"></label>
          <label class="ig-bk__field"><span>Email <i aria-hidden="true">*</i></span><input type="email" name="email" autocomplete="email" maxlength="160" required></label>
          <label class="ig-bk__field"><span>Phone <em>(optional)</em></span><input type="tel" name="phone" autocomplete="tel" inputmode="tel" maxlength="30"></label>
          <label class="ig-bk__field"><span>Nearest office <i aria-hidden="true">*</i></span>
            <select name="office" required>
<?php if (!isset($office['slug'])): ?>
              <option value="" selected disabled>Choose your closest office</option>
<?php endif; ?>
<?php foreach (locations() as $slug => $each): ?>
              <option value="<?= e($slug) ?>"<?= ($office['slug'] ?? '') === $slug ? ' selected' : '' ?>><?= e($each['name']) ?></option>
<?php endforeach; ?>
            </select>
          </label>
          <label class="ig-bk__field ig-bk__field--full"><span>Anything you want us to look at? <em>(optional)</em></span><textarea name="notes" rows="3" maxlength="1000"></textarea></label>
        </div>

        <label class="ig-bk__consent">
          <input type="checkbox" name="consent" value="1" required>
          <span>I agree that Ignite Orthodontics may use my photo to prepare a smile preview and to contact me about treatment. I understand the preview is an illustration, not a treatment plan or a promise of results, and that my photo is stored privately and deleted afterwards. This is covered by our <a href="/privacy-policy/" target="_blank" rel="noopener">Privacy Policy</a> and <a href="/terms-and-conditions/" target="_blank" rel="noopener">Terms and Conditions</a>.</span>
        </label>

        <p class="ig-bk__error" data-sm-error role="alert" hidden></p>
        <button class="ig-bk__next ig-bk__submit" type="submit" data-sm-submit>See My Smile</button>
        <p class="ig-bk__note">No cost and no obligation.</p>
      </form>
    </div>

    <aside class="bk-side">
      <div class="bk-box">
        <span class="bk-box__label">How it works</span>
        <ol class="vc-how">
          <li><b>Send a photo</b><span>One clear photo of you smiling.</span></li>
          <li><b>See the difference</b><span><?= $aiOn ? 'You get a preview of a straighter smile straight away.' : 'Our team puts together what treatment would change.' ?></span></li>
          <li><b>Hear from a specialist</b><span>We tell you which treatment would suit, how long and what it costs.</span></li>
        </ol>
      </div>

      <div class="bk-box">
        <span class="bk-box__label">Your photo</span>
        <ul class="bk-checks">
          <li><?= $check ?>Seen only by our orthodontic team</li>
          <li><?= $check ?>Never posted or shared anywhere</li>
          <li><?= $check ?>Stored privately and deleted afterwards</li>
          <li><?= $check ?>Previews are illustrations, not promises</li>
        </ul>
      </div>

      <div class="bk-box bk-call">
        <span class="bk-box__label">Rather talk it through?</span>
        <a class="bk-call__btn" href="/virtual-consultation/">Book a video consultation</a>
      </div>
    </aside>
  </div>
</div>
