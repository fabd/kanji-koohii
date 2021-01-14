<?php
  use_helper('Form', 'Validation');

  $posts = $post ? [$post] : false;
?>
<div class="row">

  <div class="col-md-9">

<?php if ($posts !== false): ?>

    <h2>Post preview</h2>

  <?php include_partial('news/list', ['posts' => $posts, 'post_preview' => true]); ?>

<?php endif ?>

    <h2>New Post</h2>
<?php
    echo form_errors();
    echo form_tag('news/post', ['class'=>'', 'autocomplete' => 'false']);
?>


    <div class="form-group">
      <div class="flex flex-g-s">
        <div class="col-m col-g">
          <label for="name">Id</label><br>
          <?php echo input_tag('post_id', 0, ['class' => 'form-control']) ?>
        </div>
        <div class="col-m-9 col-g">
          <label for="name">Title</label>
          <?php echo input_tag('post_title', '', ['class' => 'form-control']) ?>
        </div>
        <div class="col-m-2 col-g">
          <label for="name">Published</label>
          <?php echo input_tag('post_date', '', ['class' => 'form-control']) ?>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label for="message">Body</label>
      <?php echo textarea_tag('post_body', '', ['style' => 'min-height:600px', 'class' => 'form-control']) ?>
    </div>

    <div class="form-group">
    </div>

    <div class="form-group">
      <?php echo submit_tag('Post', ['class' => 'btn btn-success']) ?>&nbsp;&nbsp;
      <input type="submit" name="do_preview" value="Preview" class="btn btn-primary">
    </div>

    </form>

  </div><!-- /col -->

  <div class="col-md-3">
    <?php include_partial('archiveList') ?>
  </div>

</div>
