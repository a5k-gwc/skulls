<?php die(); ?>
### Settings ###
ServerSignature Off
DirectoryIndex index.html
Options -Indexes
FileETag None

<IfModule mod_headers.c>
  Header unset ETag
  Header unset X-Powered-By
</IfModule>

### Block bad clients ###
<Files "skulls.php">
  # Block Shareaza without version (they abuse server resources)
  BrowserMatch "^Shareaza$" GoAway=1
  Order Deny,Allow
  Deny from Env=GoAway
</Files>

### Charset ###
AddDefaultCharset UTF-8
AddCharset        UTF-8 .css .js

### MIME types ###
AddType text/html              .html .htm
AddType text/css               .css
AddType application/javascript .js
AddType image/gif              .gif
AddType image/png              .png
AddType image/x-icon           .ico

### Enable caching ###
<IfModule mod_expires.c>
  ExpiresActive On

  # 1 days - Check also the extension to avoid setting it also on other files (like .php)
  <FilesMatch "\.(html|htm)$">
    ExpiresByType text/html             A86400
  </FilesMatch>
  # 1 day
  ExpiresByType text/css               A86400
  ExpiresByType application/javascript A86400
  ExpiresByType text/javascript        A86400
  # 7 days
  ExpiresByType image/gif              A604800
  ExpiresByType image/png              A604800
  ExpiresByType image/x-icon           A604800
</IfModule>

### Enable compression ###
<IfModule mod_deflate.c>
  <IfModule mod_filter.c>
    <FilesMatch "\.(html|htm)$">
      # Check also the extension to avoid setting it also on other files (like .php) that could set compression by itself
      AddOutputFilterByType DEFLATE text/html
    </FilesMatch>
    AddOutputFilterByType DEFLATE text/css application/javascript text/javascript
  </IfModule>

  <IfModule !mod_filter.c>
    <FilesMatch "\.(html|htm|css|js)$">
      SetOutputFilter DEFLATE
    </FilesMatch>
  </IfModule>
</IfModule>

### Deny access to .htaccess ###
<Files ".htaccess">
  Order Allow,Deny
  Deny from All
</Files>
<Files "base-htaccess.php">
  Order Allow,Deny
  Deny from All
</Files>

# This should block, for the GWC, the extension strip that is enabled on some servers
RedirectMatch permanent "^(.*)\/skulls$" $1/skulls.php
