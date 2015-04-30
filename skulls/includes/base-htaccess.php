<?php die(); ?>
### Main settings ###
ServerSignature Off
DirectorySlash On
DirectoryIndex index.html
Options -Indexes
FileETag None

<IfModule mod_headers.c>
  Header unset X-Powered-By
</IfModule>

### Block bad clients ###
<Files "skulls.php">
  # Block Shareaza without version (they abuse server resources)
  BrowserMatch "^Shareaza$" GoAway=1
  Order Deny,Allow
  Deny from Env=GoAway
</Files>

### Redirection  ###
<IfModule mod_rewrite.c>
  RewriteEngine On

  <Files "skulls">
    # This should block, for the main php script, the extension strip that is enabled on some servers by re-adding the extension
    RewriteCond "%{REQUEST_FILENAME}\.php" -f
    RewriteCond "%{REQUEST_URI}" "^(.*)/skulls$"
    RewriteRule "" "%1/skulls.php" [L,R=permanent]
  </Files>

  # Redirect from the www url to the not-www url (excluding the main php script that already auto-redirect itself)
  RewriteCond "%{HTTPS}" "off"
  RewriteCond "%{REQUEST_URI}" "!/skulls\.php$"
  RewriteCond "%{HTTP_HOST}" "^www\.(.+)$"
  RewriteRule "" "http://%1%{REQUEST_URI}" [L,R=permanent]

  RewriteCond "%{HTTPS}" "on"
  RewriteCond "%{REQUEST_URI}" "!/skulls\.php$"
  RewriteCond "%{HTTP_HOST}" "^www\.(.+)$"
  RewriteRule "" "https://%1%{REQUEST_URI}" [L,R=permanent]
</IfModule>

### Set charsets ###
AddDefaultCharset UTF-8
AddCharset        UTF-8 .css .js

### Set MIME types ###
AddType text/html              .html .htm
AddType text/css               .css
AddType application/javascript .js
AddType image/gif              .gif
AddType image/png              .png
AddType image/x-icon           .ico

### Enable caching ###
<IfModule mod_expires.c>
  ExpiresActive On

  # 1 day - Check also the extension to avoid setting it also on other files (like .php)
  <FilesMatch "\.(html|htm)$">
    ExpiresByType text/html             A86400
  </FilesMatch>
  # 1 day
  ExpiresByType application/javascript A86400
  ExpiresByType text/javascript        A86400
  # 7 days
  ExpiresByType text/css               A604800
  # 7 days
  ExpiresByType image/gif              A604800
  ExpiresByType image/png              A604800
  ExpiresByType image/x-icon           A604800
</IfModule>

### Enable compression ###
<IfModule mod_deflate.c>
  # Apache 2.0.26 and later
  RemoveOutputFilter .php

  # Apache 2.0.33 and later
  <IfModule mod_filter.c>
    <FilesMatch "\.(html|htm)$">
      # Check also the extension to avoid setting it also on other files (like .php) that could set compression by itself
      AddOutputFilterByType DEFLATE text/html
    </FilesMatch>
    AddOutputFilterByType DEFLATE text/css application/javascript text/javascript
  </IfModule>

  # Apache all
  <IfModule !mod_filter.c>
    <FilesMatch "\.(html|htm|css|js)$">
      SetOutputFilter DEFLATE
    </FilesMatch>
  </IfModule>
</IfModule>

### Deny access to .htaccess ###
<Files ".htaccess">
  Deny from All
</Files>
<Files "base-htaccess.php">
  Deny from All
</Files>
