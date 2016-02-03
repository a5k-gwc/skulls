<?php die(); ?>

## Block bad clients ###
<Files "skulls.php">
  # Block Shareaza without version (it abuse server resources, it is blocked also inside the php script)
  BrowserMatch "^Shareaza$" GoAway=1
  Order Deny,Allow
  Deny from Env=GoAway
</Files>
