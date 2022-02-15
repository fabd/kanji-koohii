<div class="pt-8">

  <div class="col-side">
    <?php echo image_tag('/images/1.0/nav/about_photo.gif', 'size=138x138') ?>
    <div style="padding-left:8px">
      <?php echo link_to('Contact', '@contact') ?>
    </div>
  </div>

  <div class="markdown">
    <div class="col-box learn-more">

      <section class="mb-8">
        <h2>License Information</h2>
        <p>
          All user-contributed content on this website is licensed under <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/">Creative Commons BY-NC-SA</a>.
        </p>
      </section>  

      <section class="mb-8">
        <h2>Attribution Requirements</h2>
        <p>
          Please note the attribution clause in the license:
        </p>
        <p class="actionconfirmationmessage">
          <strong>Attribution</strong> — You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).
        </p>
        <p>
If you republish this content on a WWW server or software package, we require that you:
        </p>
        <ul>
          <li>
<strong>Visually indicate that the content is from <?php _CJ('Kanji Koohii!') ?></strong> in some way. The acknowledgement doesn’t have to be obnoxious; a discreet footer text is fine.
          </li>
          <li>
<strong>Hyperlink each set of stories directly to the original Study page</strong> on this website
(e.g.: <?php echo link_to(sfConfig::get('app_website_url').'/study/kanji/2011', '@study_edit?id=2011') ?>)
          </li>
          <li>
<strong>Show the author names</strong> for every published story
          </li>
          <li>
<strong>Hyperlink each author name</strong> directly back to their user profile page on this website
(e.g.: <?php echo link_to(sfConfig::get('app_website_url').'/profile/fuaburisu', 'profile/index?username=fuaburisu') ?> )
          </li>
        </ul>
        <p>
Each hyperlink must point directly to our domain, must be visible even with Javascript disabled, and
not use tinyurl or any other form of url obfuscation or redirection.
Links must not use the “nofollow” attribute.
        </p>
        <p>
          These attribution requirements are in the spirit of <strong>fair attribution</strong>
          to our service and more importantly, to the individuals who have
          generously invested their time to contribute valuable content.
        </p>
        <p>
Thank you!
        </p>

      </section>
      
    </div>
  </div>

</div>
