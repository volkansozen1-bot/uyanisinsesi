# Siteyi GitHub'daki son surume gunceller ve bekleyen veritabani guncellemelerini calistirir.
# Windows sunucuda, sitenin klasorunde (app ve public klasorlerinin oldugu yer) calistirin:
#
#   powershell -ExecutionPolicy Bypass -File .\bin\deploy.ps1
#
# Ilk kurulum, otomatik calistirma ve ayrintilar: README > "Windows sunucu: PowerShell ile guncelleme"
# .env, storage\logs ve public\uploads repoda olmadigi icin bu betik onlara dokunmaz.

param(
    [string]$SitePath = (Split-Path -Parent $PSScriptRoot),
    [string]$Repo     = 'https://github.com/volkansozen1-bot/uyanisinsesi.git',
    [string]$Branch   = 'main',
    [string]$Php      = ''
)

# Native komutlarin (git, php) stderr ciktisi Windows PowerShell 5.1'de hata sayilmasin; cikis kodlari elle kontrol edilir
$ErrorActionPreference = 'Continue'
$logFile = Join-Path $SitePath 'storage\logs\deploy.log'

function Write-Log([string]$message) {
    $line = '{0}  {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $message
    Write-Host $line
    try { Add-Content -Path $logFile -Value $line -Encoding UTF8 } catch { }
}

function Invoke-Git {
    # safe.directory: zamanlanmis gorev farkli kullaniciyla (SYSTEM) calistiginda git klasoru reddetmesin
    & git -c safe.directory=* -C $SitePath @args
    if ($LASTEXITCODE -ne 0) { throw "git $($args -join ' ') basarisiz (kod $LASTEXITCODE)" }
}

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Host 'Git kurulu degil. Kurmak icin:  winget install --id Git.Git -e   (sonra PowerShell penceresini kapatip acin)'
    exit 1
}
if (-not (Test-Path (Join-Path $SitePath 'public'))) {
    Write-Host "Site klasoru bulunamadi: $SitePath (icinde public klasoru olmali). -SitePath ile belirtin."
    exit 1
}
New-Item -ItemType Directory -Force -Path (Split-Path $logFile) | Out-Null

try {
    if (-not (Test-Path (Join-Path $SitePath '.git'))) {
        Write-Log "Ilk kurulum: $SitePath GitHub'a baglaniyor"
        Invoke-Git init -q
        Invoke-Git remote add origin $Repo
    }

    $before = (& git -c safe.directory=* -C $SitePath rev-parse --short --verify --quiet HEAD)
    if (-not $before) { $before = '(yok)' }
    Invoke-Git fetch -q --depth 1 origin $Branch
    $after = (& git -c safe.directory=* -C $SitePath rev-parse --short --verify --quiet FETCH_HEAD)
    if (-not $after) { throw "GitHub'dan $Branch dali alinamadi" }

    if ($before -eq $after) {
        Write-Log "Guncel ($after), degisiklik yok"
    } else {
        # Repodaki dosyalari GitHub'daki haline getirir; repoda olmayan dosyalara (.env, yuklenenler) dokunmaz
        Invoke-Git reset -q --hard FETCH_HEAD
        Write-Log "Guncellendi: $before -> $after"
    }

    # PHP'yi bul: parametre, PATH, sonra yaygin klasorler
    if (-not $Php) {
        $cmd = Get-Command php -ErrorAction SilentlyContinue
        if ($cmd) { $Php = $cmd.Source }
    }
    if (-not $Php) {
        # IIS: FastCGI'da kayitli php-cgi.exe'nin yanindaki php.exe
        try {
            Import-Module WebAdministration -ErrorAction Stop
            $cgi = (Get-WebConfiguration system.webServer/fastCgi/application | Select-Object -First 1).fullPath
            if ($cgi) {
                $candidate = Join-Path (Split-Path ([Environment]::ExpandEnvironmentVariables($cgi))) 'php.exe'
                if (Test-Path $candidate) { $Php = $candidate }
            }
        } catch { }
    }
    if (-not $Php) {
        $Php = @('C:\xampp\php\php.exe', 'C:\php\php.exe', 'C:\Program Files\PHP\php.exe') |
            Where-Object { Test-Path $_ } | Select-Object -First 1
    }
    if (-not $Php) {
        Write-Log 'PHP bulunamadi; veritabani guncellemesi atlandi. -Php "C:\...\php.exe" ile belirtin.'
        exit 0
    }

    $output = & $Php (Join-Path $SitePath 'bin\migrate.php') 2>&1
    $output | ForEach-Object { Write-Log "  $_" }
    if ($LASTEXITCODE -ne 0) { throw 'Veritabani guncellemesi basarisiz' }
}
catch {
    Write-Log "HATA: $($_.Exception.Message)"
    exit 1
}
