<?php require_once dirname(__FILE__) . '/config.php';?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Release Manager</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <?php $css_ver = filemtime(dirname(__FILE__) . '/assets/main.css'); ?>
    <link href="assets/main.css?v=<?php echo $css_ver; ?>" rel="stylesheet" />

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <?php $js_ver = filemtime(dirname(__FILE__) . '/assets/main.js'); ?>
    <script src="assets/main.js?v=<?php echo $js_ver; ?>"></script>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-2">
      <div class="container">
        <a class="navbar-brand" href="#">
          <i class="bi bi-rocket-takeoff"></i> Release Manager
        </a>
        <form class="d-flex ms-3 position-relative" role="search" onsubmit="return false;">
          <input id="orbisius-release-manager-filter" class="form-control form-control-sm pe-4" type="text" placeholder="Filter plugins..." aria-label="Filter plugins" name="orbisius_filter_nonautofill" autocomplete="nope" />
          <i id="orbisius-release-manager-filter-clear" class="bi bi-x-circle orbisius-release-manager-filter-clear"></i>
        </form>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="https://orbisius.com/about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="https://orbisius.com/contact">Contact</a></li>
        </ul>
      </div>
    </nav>

    <div class="container">

      <div class="row">
        <div class="col-lg-12">

			<?php
				$base_dir_esc = htmlentities(dirname(__FILE__));
				echo "<input class='full_width form-control form-control-sm bg-light' type='text' value='$base_dir_esc' onclick='this.select();' readonly />" . APP_NL;
			?>
            <div id="orbisius-release-manager-plugin-count" class="orbisius-release-manager-plugin-count">
                Plugins: <span id="orbisius-release-manager-plugin-count-value"></span>
            </div>

