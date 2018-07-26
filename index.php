<?php
session_start();
require_once(__DIR__.'/config.php');
require_once(__DIR__.'/imageUploader.php');

$uploader = new \MyApp\ImageUploader();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uploader->upload();
}
list($success, $error) = $uploader->getResults();
$images = $uploader->getImages();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Image Uploader</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <div class="btn">
    Upload!
    <form action="" method="post" enctype="multipart/form-data" id="my_form">
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
      <input type="file" name="image" id="my_file">
      <?php /*<input type="submit" value="upload">*/ ?>
    </form>
  </div>

  <?php if (isset($success)): ?>
    <div class="msg success"><?php echo h($success); ?></div>
  <?php endif; ?>
  <?php if (isset($error)): ?>
    <div class="msg error"><?php echo h($error); ?></div>
  <?php endif; ?>

  <ul>
    <?php foreach ($images as $image): ?>
    <li>
      <a href="<?php echo h(basename(IMAGES_DIR).'/'.basename($image)); ?>">
        <img src="<?php echo h($image); ?>" alt="">
      </a>
    </li>
    <?php endforeach; ?>
  </ul>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="script.js" type="text/javascript"></script>
</body>
</html>
