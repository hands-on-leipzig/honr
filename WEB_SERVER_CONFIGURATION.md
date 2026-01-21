# Web Server Configuration Guide

## Problem

Browser shows "Forbidden" for `https://test.honr.hands-on-technology.org`

This means the web server is running but not configured to serve the Laravel application.

## Solution

Configure the web server (Nginx or Apache) to serve the Laravel application from:
```
/var/www/honr/current/backend/public
```

## For Nginx

### Step 1: Create Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/honr-test
```

### Step 2: Add This Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name test.honr.hands-on-technology.org;
    
    # Redirect HTTP to HTTPS (if SSL is configured)
    # return 301 https://$server_name$request_uri;
    
    # For now, serve on HTTP (or configure SSL later)
    root /var/www/honr/current/backend/public;
    index index.php index.html;

    # Frontend SPA - serve static files
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Laravel backend - PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;  # Adjust PHP version if needed
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Storage files
    location /storage {
        alias /var/www/honr/shared/storage/app/public;
        try_files $uri =404;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Deny access to sensitive files
    location ~* \.(env|git|svn|htaccess|htpasswd)$ {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Logging
    access_log /var/log/nginx/honr-test-access.log;
    error_log /var/log/nginx/honr-test-error.log;
}
```

**Important:** Adjust `php8.4-fpm.sock` to match your PHP version:
- PHP 8.2: `unix:/var/run/php/php8.2-fpm.sock`
- PHP 8.3: `unix:/var/run/php/php8.3-fpm.sock`
- PHP 8.4: `unix:/var/run/php/php8.4-fpm.sock`

### Step 3: Enable the Site

```bash
# Create symlink to enable site
sudo ln -s /etc/nginx/sites-available/honr-test /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

### Step 4: Verify PHP-FPM is Running

```bash
# Check PHP-FPM status
sudo systemctl status php8.4-fpm

# If not running, start it
sudo systemctl start php8.4-fpm
sudo systemctl enable php8.4-fpm
```

## For Apache

### Step 1: Create Apache Configuration

```bash
sudo nano /etc/apache2/sites-available/honr-test.conf
```

### Step 2: Add This Configuration

```apache
<VirtualHost *:80>
    ServerName test.honr.hands-on-technology.org
    DocumentRoot /var/www/honr/current/backend/public

    <Directory /var/www/honr/current/backend/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Storage symlink
    Alias /storage /var/www/honr/shared/storage/app/public
    <Directory /var/www/honr/shared/storage/app/public>
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/honr-test-error.log
    CustomLog ${APACHE_LOG_DIR}/honr-test-access.log combined
</VirtualHost>
```

### Step 3: Enable Required Modules

```bash
sudo a2enmod rewrite
sudo a2enmod headers
```

### Step 4: Enable the Site

```bash
# Enable site
sudo a2ensite honr-test.conf

# Test configuration
sudo apache2ctl configtest

# If test passes, reload Apache
sudo systemctl reload apache2
```

## Verify Configuration

### Test on Server

```bash
# Test from server itself
curl http://localhost/up
# Should return: {"status":"ok"}

curl http://localhost/api/ping
# Should return: {"status":"ok","timestamp":"..."}
```

### Test from Browser

Visit: `https://test.honr.hands-on-technology.org`

Should show the application (not "Forbidden").

## Common Issues

### Issue: Still Getting "Forbidden"

**Check file permissions:**
```bash
# Ensure web server user owns the files
sudo chown -R www-data:www-data /var/www/honr
sudo chmod -R 755 /var/www/honr
sudo chmod -R 775 /var/www/honr/shared/storage
```

**Check document root:**
```bash
# Verify the path exists
ls -la /var/www/honr/current/backend/public
# Should show index.php and other files
```

### Issue: 500 Internal Server Error

**Check Laravel logs:**
```bash
tail -50 /var/www/honr/shared/storage/logs/laravel.log
```

**Check PHP-FPM logs:**
```bash
sudo tail -50 /var/log/php8.4-fpm.log
```

**Check web server error logs:**
```bash
# Nginx
sudo tail -50 /var/log/nginx/honr-test-error.log

# Apache
sudo tail -50 /var/log/apache2/honr-test-error.log
```

### Issue: PHP Files Not Executing

**Check PHP-FPM is running:**
```bash
sudo systemctl status php8.4-fpm
```

**Check socket path matches configuration:**
```bash
# Find PHP-FPM socket
ls -la /var/run/php/
# Should show php8.4-fpm.sock (or your PHP version)
```

**Update Nginx config** if socket path is different.

## SSL/HTTPS Configuration (Optional, for later)

Once HTTP is working, you can add SSL:

```bash
# Install Certbot (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d test.honr.hands-on-technology.org

# Certbot will automatically update Nginx config
```

## Quick Checklist

- [ ] Web server configuration file created
- [ ] Document root points to `/var/www/honr/current/backend/public`
- [ ] Site enabled (symlink created)
- [ ] Configuration tested (`nginx -t` or `apache2ctl configtest`)
- [ ] Web server reloaded
- [ ] PHP-FPM running
- [ ] File permissions correct
- [ ] Test from server: `curl http://localhost/up`
- [ ] Test from browser: `https://test.honr.hands-on-technology.org`

## After Configuration

Once the web server is configured:

1. **Test health endpoint:**
   ```bash
   curl https://test.honr.hands-on-technology.org/up
   ```

2. **Test API endpoint:**
   ```bash
   curl https://test.honr.hands-on-technology.org/api/ping
   ```

3. **Next deployment** will pass the health check automatically!
