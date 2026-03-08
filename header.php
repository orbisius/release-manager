<?php require_once dirname(__FILE__) . '/config.php';?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Release Manager</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <link href="assets/main.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="assets/main.js"></script>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="#">
          <i class="bi bi-rocket-takeoff"></i> Release Manager
        </a>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="//orbisius.com/about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="//orbisius.com/contact">Contact</a></li>
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

