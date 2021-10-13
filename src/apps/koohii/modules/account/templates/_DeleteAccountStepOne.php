<section class="padded-box rounded text-smx mt-12">

  <h2 class="mb-3">Delete Account</h2>

  <p class="mb-3">We will <span class="font-bold">immediately delete</span> all of your account information including:</p>

  <ul>
    <li class="mb-1">all your private <u>and shared stories</u></li>
    <li class="mb-1">your kanji flashcard selection, along with selected vocab & SRS review status</li>
    <li class="mb-1">your account registration information (username, email, etc)</li>
  </ul>

  <?= link_to('Yes,  I want to delete my account ( step 1 of 2 )', 'account/delete', ['class' => 'btn btn-danger']); ?>

</section>