<?php
  use_helper('Form', 'Validation');

  $posts = $post ? [$post] : false;
?>
<div class="row">

  <div class="col-lg-9">

<?php if ($posts !== false): ?>

    <h2>Post preview</h2>

  <?php include_partial('news/list', ['posts' => $posts, 'post_preview' => true]); ?>

<?php endif ?>

    <h2>New Post</h2>

<?php if (KK_ENV_DEV && $sf_response::USE_DEV_SERVER === true): ?>
  <div class="bg-[red] text-[#fff] p-4 mb-4 rounded">
    WARNING: VITE DEV SERVER may reload the page if editing & saving code!
  </div>
<?php endif; ?>

<?php
    echo form_errors();
    echo form_tag('news/post', ['class'=>'', 'autocomplete' => 'false']);
?>


    <div class="form-group">
      <div class="flex flex-nowrap -mx-1">
        <div class="w-[136px] mx-1">
          <label class="form-label" for="name">Id</label><br>
          <?php echo input_tag('post_id', 0, ['class' => 'form-control']) ?>
        </div>
        <div class="w-9/12 mx-1">
          <label class="form-label" for="name">Title</label>
          <?php echo input_tag('post_title', '', ['class' => 'form-control']) ?>
        </div>
        <div class="w-2/12 mx-1">
          <label class="form-label" for="name">Published</label>
          <?php echo input_tag('post_date', '', ['class' => 'form-control']) ?>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label" for="message">Body</label>
      <?php echo textarea_tag('post_body', '', ['style' => 'min-height:600px', 'class' => 'form-control']) ?>
    </div>

    <div class="form-group">
    </div>

    <div class="form-group">
      <?php echo submit_tag('Post', ['class' => 'ko-Btn ko-Btn--success ko-Btn--large']) ?>
      <input type="submit" name="do_preview" value="Preview" class="ko-Btn ko-Btn--primary ko-Btn--large ml-2">
    </div>

    </form>

  </div><!-- /col -->

  <div class="col-lg-3">
    <?php include_partial('archiveList') ?>
  </div>

</div>
