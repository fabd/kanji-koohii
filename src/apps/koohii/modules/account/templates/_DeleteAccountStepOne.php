<section class="padded-box rounded mt-12 mb-8">

  <h2 class="mb-4">Delete Account</h2>
  
  <div class="text-sm markdown" style="--vunit: 1.1rem">

    <p class="">We will <span class="font-bold">immediately delete</span> all of your account information including:</p>

    <ul>
      <li>all your private <u>and shared stories</u></li>
      <li>your kanji flashcard selection, along with selected vocab & SRS review status</li>
      <li>your account registration information (username, email, etc)</li>
    </ul>

    <?= link_to(
        'Yes,  I want to delete my account ( step 1 of 2 )',
        'account/delete',
        ['class' => 'ko-Btn ko-Btn--danger']
      );
    ?>
  
  </div>
</section>