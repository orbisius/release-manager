        </div>

        <?php if (0) : ?>
        <div class="col-lg-4">
          <h4>Plugins</h4>
          <p>
              <?php foreach ( $plugin_info_rec as $plugin_name => $ver ) : ?>
                <?php echo $plugin_name . ' ' . $ver; ?><br/>
              <?php endforeach; ?>
          </p>
        </div>
        <?php endif; ?>
      </div>

      <footer class="footer">
        <p>&copy; Orbisius 2014-<?php echo date('Y'); ?></p>
      </footer>

    </div> <!-- /container -->

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="share/bootstrap-3.3.1-dist/js/bootstrap.min.js"></script>
  </body>
</html>
