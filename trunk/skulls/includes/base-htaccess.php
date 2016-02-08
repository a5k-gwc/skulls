<?php header('Connection: close'); header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden'); die; ?>
### Main settings ###
ServerSignature Off
DirectorySlash On
DirectoryIndex index.html
Options -Indexes
FileETag All -INode

<IfModule mod_headers.c>
  Header unset X-Powered-By
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

### Enable compression (excluding php files) ###
<IfModule mod_deflate.c>
  <FilesMatch "\.(html?|css|js)$">
    FileETag None
    <IfModule mod_headers.c>
      Header set Vary Accept-Encoding
    </IfModule>
  </FilesMatch>

  # Apache 2.0.33 and later
  <IfModule mod_filter.c>
    # Check also extension to avoid compressing also php files (they are also served as text/html) that set compression by themself
    <FilesMatch "\.html?$">
      AddOutputFilterByType DEFLATE text/html
    </FilesMatch>
    AddOutputFilterByType DEFLATE text/css application/javascript text/javascript
  </IfModule>

  <IfModule !mod_filter.c>
    <FilesMatch "\.(html?|css|js)$">
      SetOutputFilter DEFLATE
    </FilesMatch>
  </IfModule>
</IfModule>

### Deny access to .htaccess ###
<Files ".htaccess">
  Order Allow,Deny
</Files>
