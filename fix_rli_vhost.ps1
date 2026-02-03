# fix_rli_vhost.ps1
# Run as Administrator
if (-not ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Error "Script must be run as Administrator. Re-run PowerShell as Admin and try again."
    exit 1
}

$httpd      = 'C:\xampp\apache\conf\httpd.conf'
$vhosts     = 'C:\xampp\apache\conf\extra\httpd-vhosts.conf'
$hostsFile  = 'C:\Windows\System32\drivers\etc\hosts'
$errorLog   = 'C:\xampp\apache\logs\error.log'

# backups
$timestamp = (Get-Date).ToString('yyyyMMdd-HHmmss')
Copy-Item -Path $httpd -Destination "${httpd}.bak.${timestamp}" -Force
Copy-Item -Path $vhosts -Destination "${vhosts}.bak.${timestamp}" -Force
Copy-Item -Path $hostsFile -Destination "${hostsFile}.bak.${timestamp}" -Force
Write-Output "Backups created with timestamp $timestamp"

# 1) Ensure vhosts file contains our VirtualHost block (idempotent)
$vhostBlock = @"
<VirtualHost *:80>
    ServerName rli.local
    DocumentRoot "C:/xampp/htdocs/RLI-materialsReq"
    <Directory "C:/xampp/htdocs/RLI-materialsReq">
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog "logs/rli.local-error.log"
    CustomLog "logs/rli.local-access.log" common
</VirtualHost>
"@

$vhostsContent = Get-Content -Raw -ErrorAction Stop $vhosts
if ($vhostsContent -match 'ServerName\s+rli\.local') {
    Write-Output "VirtualHost for rli.local already present in $vhosts"
} else {
    Add-Content -Path $vhosts -Value "`n$vhostBlock"
    Write-Output "Appended vhost block to $vhosts"
}

# 2) Ensure httpd.conf loads mod_rewrite and includes vhosts
$httpdText = Get-Content -Raw -ErrorAction Stop $httpd

# Uncomment LoadModule rewrite_module if commented
if ($httpdText -match '^\s*#\s*LoadModule\s+rewrite_module\s+modules/mod_rewrite\.so') {
    $httpdText = $httpdText -replace '^\s*#\s*(LoadModule\s+rewrite_module\s+modules/mod_rewrite\.so)', '$1'
    Write-Output "Uncommented LoadModule rewrite_module line"
} elseif ($httpdText -notmatch 'LoadModule\s+rewrite_module') {
    $httpdText = $httpdText -replace "(?s)(LoadModule\s+[^\r\n]+\r?\n)(?!.*LoadModule)", "`$1LoadModule rewrite_module modules/mod_rewrite.so`n"
    Write-Output "Inserted LoadModule rewrite_module line"
} else {
    Write-Output "LoadModule rewrite_module is already enabled"
}

# Ensure Include conf/extra/httpd-vhosts.conf is present and uncommented
if ($httpdText -match '^\s*#\s*Include\s+conf/extra/httpd-vhosts.conf') {
    $httpdText = $httpdText -replace '^\s*#\s*(Include\s+conf/extra/httpd-vhosts.conf)', '$1'
    Write-Output "Uncommented Include conf/extra/httpd-vhosts.conf"
} elseif ($httpdText -notmatch 'Include\s+conf/extra/httpd-vhosts.conf') {
    $httpdText = $httpdText + "`nInclude conf/extra/httpd-vhosts.conf`n"
    Write-Output "Appended Include conf/extra/httpd-vhosts.conf"
} else {
    Write-Output "Include conf/extra/httpd-vhosts.conf present"
}

# 3) Ensure <Directory "C:/xampp/htdocs"> block uses AllowOverride All
if ($httpdText -match '(?s)<Directory\s+"C:/xampp/htdocs">.*?</Directory>') {
    $httpdText = $httpdText -replace '(?s)(<Directory\s+"C:/xampp/htdocs">.*?AllowOverride\s+)(None|[^\r\n]+)(.*?)</Directory>', '$1All$3</Directory>'
    Write-Output "Set AllowOverride All in <Directory \"C:/xampp/htdocs\"> block (if present)"
} else {
    Write-Output "No <Directory \"C:/xampp/htdocs\"> block found in httpd.conf; please confirm manually if needed."
}

# Write changes to httpd.conf
Set-Content -Path $httpd -Value $httpdText -Force
Write-Output "Updated $httpd"

# 4) Add hosts entry if not present
$hostsText = Get-Content -Path $hostsFile -ErrorAction Stop
if ($hostsText -notmatch '^\s*127\.0\.0\.1\s+rli\.local') {
    Add-Content -Path $hostsFile -Value "`n127.0.0.1    rli.local"
    Write-Output "Added hosts entry for rli.local"
} else {
    Write-Output "hosts entry for rli.local already exists"
}

# 5) Restart Apache service (try service name Apache2.4)
$svc = Get-Service -Name 'Apache2.4' -ErrorAction SilentlyContinue
if ($null -ne $svc) {
    try {
        Restart-Service -Name 'Apache2.4' -Force -ErrorAction Stop
        Write-Output "Apache (Apache2.4) restarted successfully."
    } catch {
        Write-Warning "Failed to restart service Apache2.4 via Restart-Service; attempting net stop/start"
        net stop Apache2.4
        Start-Sleep -Seconds 2
        net start Apache2.4
    }
} else {
    Write-Warning "Service 'Apache2.4' not found. If you use XAMPP control panel, restart Apache there."
}

# 6) Show last lines of error log
if (Test-Path $errorLog) {
    Write-Output "`n--- Apache error.log (last 80 lines) ---"
    Get-Content -Path $errorLog -Tail 80 | ForEach-Object { Write-Output $_ }
} else {
    Write-Warning "Apache error.log not found at $errorLog"
}

Write-Output "`nDone. Open http://rli.local/ in your browser."
