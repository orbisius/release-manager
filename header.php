<?php require_once dirname(__FILE__) . '/config.php';?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Release Manager</title>

    <!-- Bootstrap -->
    <link href="share/bootstrap-3.3.1-dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/main.css" rel="stylesheet" />

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <div class="container">
      <div class="header">
        <nav>
          <ul class="nav nav-pills pull-right">
            <li role="presentation" class="active"><a href="#">Home</a></li>
            <li role="presentation"><a href="//orbisius.com/about">About</a></li>
            <li role="presentation"><a href="//orbisius.com/contact">Contact</a></li>
          </ul>
        </nav>
        <h3 class="text-muted">Release Manager</h3>
      </div>

      <div class="row marketing">
        <div class="col-lg-8">
		
			<?php
				echo '<input class="full_width" type="text" value="' . dirname(__FILE__) .'" onclick="this.select();" />' . APP_NL;
			?>
          
