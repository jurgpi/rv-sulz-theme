<#
  RV Sulz Theme - Build & Versionierung

  Aufruf (im Ordner CustomTemplate):
    powershell -ExecutionPolicy Bypass -File tools\build.ps1                 # aktuelle Version bauen
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump patch     # 1.5.0 -> 1.5.1 (Fehlerbehebung)
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump minor     # 1.5.0 -> 1.6.0 (neue Funktion)
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump major     # 1.5.0 -> 2.0.0 (grosser Umbau)

  - Die Version steht nur im Kopf von rv-sulz\style.css.
  - Bei -Bump wird in CHANGELOG.md ein neuer Abschnitt angelegt (bitte danach ausfuellen).
  - Ergebnis: dist\rv-sulz-<version>.zip  (Ordner im ZIP heisst immer "rv-sulz",
    damit WordPress beim Hochladen das bestehende Theme ersetzt).
  - Eine bereits gebaute Version wird nicht ueberschrieben (ausser mit -Force), damit nie
    zwei verschiedene Staende dieselbe Nummer tragen.

  Git (optional):
    ... build.ps1 -Release          # alle Aenderungen als "Version x.y.z" committen + Tag vx.y.z
    ... build.ps1 -Release -Push    # zusaetzlich zu GitHub hochladen (Commit + Tag + Release mit ZIP)
    ... build.ps1 -Force -GitHubRelease  # nur GitHub-Release fuer die aktuelle Version nachholen
  Typischer Ablauf fuer eine neue Version:
    1. build.ps1 -Bump patch        (Version erhoehen, CHANGELOG-Abschnitt anlegen)
    2. CHANGELOG.md ausfuellen, testen
    3. build.ps1 -Force -Release -Push
#>
param(
  [ValidateSet('none', 'patch', 'minor', 'major')]
  [string]$Bump = 'none',
  [switch]$Force,
  [switch]$Release,
  [switch]$Push,
  [switch]$GitHubRelease
)

<#
  GitHub-Release mit dem ZIP als Anhang anlegen (oder aktualisieren).
  Das Theme laedt Updates aus diesem Anhang (inc/updater.php).
  Der GitHub-Zugang kommt aus der gespeicherten Git-Anmeldung (Git Credential Manager)
  und wird nie ausgegeben.
#>
function Publish-GitHubRelease {
  param([string]$Root, [string]$Version, [string]$Tag, [string]$ZipPath, [string]$ChangelogPath)

  [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

  $remote = (git -C $Root remote get-url origin).Trim()
  $m = [regex]::Match($remote, 'github\.com[/:]([^/]+/[^/]+?)(\.git)?$')
  if (-not $m.Success) { throw "Remote ist kein GitHub-Repository: $remote" }
  $slug = $m.Groups[1].Value

  # Token aus dem Git Credential Manager (ohne Rueckfrage). Die Anfrage geht ueber eine
  # Datei an git, weil PowerShell 5.1 Pipe/stdin fuer git unbrauchbar veraendert.
  # Die Datei enthaelt nur die Anfrage (Protokoll + Host), nie den Token.
  $env:GCM_INTERACTIVE = 'never'; $env:GIT_TERMINAL_PROMPT = '0'
  $req = Join-Path $env:TEMP ("credreq-" + [guid]::NewGuid().ToString('N') + '.txt')
  [IO.File]::WriteAllBytes($req, [Text.Encoding]::ASCII.GetBytes("protocol=https`nhost=github.com`n`n"))
  try { $credOut = cmd /c "git credential fill < `"$req`" 2>nul" } finally { Remove-Item $req -Force -ErrorAction SilentlyContinue }
  $token = ($credOut | Where-Object { $_ -like 'password=*' } | Select-Object -First 1)
  if (-not $token) { throw 'Keine gespeicherte GitHub-Anmeldung gefunden (einmal git push im Terminal ausfuehren).' }
  $token = $token.Substring(9)

  $headers = @{
    'Authorization'        = "Bearer $token"
    'Accept'               = 'application/vnd.github+json'
    'X-GitHub-Api-Version' = '2022-11-28'
    'User-Agent'           = 'theme-build-script'
  }

  # Release-Text = Abschnitt dieser Version aus CHANGELOG.md
  $notes = ''
  if (Test-Path $ChangelogPath) {
    $cl = [IO.File]::ReadAllText($ChangelogPath, (New-Object Text.UTF8Encoding $false))
    $start = $cl.IndexOf("## [$Version]")
    if ($start -ge 0) {
      $next = $cl.IndexOf("`n## [", $start + 5)
      $section = if ($next -gt 0) { $cl.Substring($start, $next - $start) } else { $cl.Substring($start) }
      $notes = ($section -split "`n", 2)[1].Trim()
    }
  }

  $api = "https://api.github.com/repos/$slug"
  $release = $null
  try { $release = Invoke-RestMethod -Method Get -Uri "$api/releases/tags/$Tag" -Headers $headers } catch { $release = $null }

  if (-not $release) {
    $payload = @{ tag_name = $Tag; name = "Version $Version"; body = $notes; draft = $false; prerelease = $false } | ConvertTo-Json
    $bytes = [Text.Encoding]::UTF8.GetBytes($payload)
    $release = Invoke-RestMethod -Method Post -Uri "$api/releases" -Headers $headers -Body $bytes -ContentType 'application/json; charset=utf-8'
    Write-Host "GitHub-Release angelegt: Version $Version"
  } else {
    Write-Host "GitHub-Release $Tag gibt es schon - Anhang wird aktualisiert."
  }

  $assetName = Split-Path $ZipPath -Leaf
  foreach ($a in @($release.assets)) {
    if ($a -and $a.name -eq $assetName) {
      Invoke-RestMethod -Method Delete -Uri "$api/releases/assets/$($a.id)" -Headers $headers | Out-Null
    }
  }
  $uploadUri = "https://uploads.github.com/repos/$slug/releases/$($release.id)/assets?name=$assetName"
  # Direkt nach dem Anlegen kennt der Upload-Server das Release manchmal noch nicht -> wiederholen
  $asset = $null
  for ($try = 1; $try -le 5 -and -not $asset; $try++) {
    try {
      $asset = Invoke-RestMethod -Method Post -Uri $uploadUri -Headers $headers -InFile $ZipPath -ContentType 'application/zip'
    } catch {
      if ($try -eq 5) { throw }
      Write-Host "Upload noch nicht moeglich (Versuch $try) - neuer Versuch in 3 Sekunden ..."
      Start-Sleep -Seconds 3
    }
  }
  Write-Host ("ZIP angehaengt: {0} ({1} KB)" -f $asset.name, [math]::Round($asset.size / 1KB))
  Write-Host ("Release: " + $release.html_url)
}

$ErrorActionPreference = 'Stop'
$name   = 'rv-sulz'
$root   = Split-Path -Parent $PSScriptRoot
$theme  = Join-Path $root $name
$style  = Join-Path $theme 'style.css'
$log    = Join-Path $theme 'CHANGELOG.md'
$dist   = Join-Path $root 'dist'
$utf8   = New-Object System.Text.UTF8Encoding $false

$css = [IO.File]::ReadAllText($style, $utf8)
$m = [regex]::Match($css, '(?m)^Version:\s*(\d+)\.(\d+)\.(\d+)\s*$')
if (-not $m.Success) { throw 'Keine Zeile "Version: x.y.z" in style.css gefunden.' }
$maj = [int]$m.Groups[1].Value; $min = [int]$m.Groups[2].Value; $pat = [int]$m.Groups[3].Value
$old = "$maj.$min.$pat"

switch ($Bump) {
  'major' { $maj++; $min = 0; $pat = 0 }
  'minor' { $min++; $pat = 0 }
  'patch' { $pat++ }
}
$version = "$maj.$min.$pat"

if ($Bump -ne 'none') {
  $css = $css.Substring(0, $m.Index) + "Version: $version" + $css.Substring($m.Index + $m.Length)
  [IO.File]::WriteAllText($style, $css, $utf8)

  $dash  = [char]0x2013
  $date  = Get-Date -Format 'yyyy-MM-dd'
  $entry = "## [$version] $dash $date`n### Neu`n- `n### Behoben`n- `n`n"
  $text  = [IO.File]::ReadAllText($log, $utf8)
  $pos   = $text.IndexOf('## [')
  if ($pos -ge 0) { $text = $text.Substring(0, $pos) + $entry + $text.Substring($pos) } else { $text += "`n" + $entry }
  [IO.File]::WriteAllText($log, $text, $utf8)
  Write-Host "Version $old -> $version (CHANGELOG.md bitte ergaenzen)"
} else {
  Write-Host "Version $version"
}

if (-not (Test-Path $dist)) { New-Item -ItemType Directory -Path $dist | Out-Null }
$zipPath = Join-Path $dist "$name-$version.zip"
if ((Test-Path $zipPath) -and -not $Force) {
  throw "dist\$name-$version.zip existiert schon. Version erhoehen (-Bump patch) oder -Force verwenden."
}
if (Test-Path $zipPath) { [IO.File]::Delete($zipPath) }

Add-Type -AssemblyName System.IO.Compression, System.IO.Compression.FileSystem
$zip = [IO.Compression.ZipFile]::Open($zipPath, 'Create')
try {
  $prefix = $theme.Length + 1
  foreach ($f in Get-ChildItem $theme -Recurse -File) {
    $rel = $name + '/' + $f.FullName.Substring($prefix).Replace([char]92, [char]47)
    [void][IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $f.FullName, $rel)
  }
} finally {
  $zip.Dispose()
}
Write-Host ("Gebaut: dist\$name-$version.zip (" + [math]::Round((Get-Item $zipPath).Length / 1KB) + ' KB)')

if ($GitHubRelease -and -not $Push) {
  # Nur das GitHub-Release fuer die aktuelle (bereits gepushte) Version anlegen/aktualisieren
  Publish-GitHubRelease -Root $root -Version $version -Tag "v$version" -ZipPath $zipPath -ChangelogPath $log
}

if ($Release -or $Push) {
  if (-not (Get-Command git -ErrorAction SilentlyContinue)) { throw 'Git ist nicht installiert.' }
  $tag = "v$version"
  $null = git -C $root rev-parse --git-dir 2>$null
  if ($LASTEXITCODE -ne 0) { throw 'Der Ordner ist kein Git-Repository.' }

  if ($Release) {
    $existing = git -C $root tag --list $tag
    if ($existing) { throw "Tag $tag gibt es schon. Version erhoehen (-Bump patch)." }
    git -C $root add -A
    $staged = git -C $root diff --cached --name-only
    if ($staged) {
      git -C $root commit -q -m "Version $version" -m "Siehe CHANGELOG.md."
      if ($LASTEXITCODE -ne 0) { throw 'Commit fehlgeschlagen.' }
      Write-Host "Commit: Version $version"
    } else {
      Write-Host 'Keine Aenderungen zu committen - Tag kommt auf den letzten Commit.'
    }
    git -C $root tag -a $tag -m "Version $version"
    if ($LASTEXITCODE -ne 0) { throw 'Tag konnte nicht angelegt werden.' }
    Write-Host "Tag: $tag"
  }

  if ($Push) {
    git -C $root push origin HEAD --follow-tags
    if ($LASTEXITCODE -ne 0) { throw 'Hochladen zu GitHub fehlgeschlagen.' }
    Write-Host 'Zu GitHub hochgeladen.'
    Publish-GitHubRelease -Root $root -Version $version -Tag $tag -ZipPath $zipPath -ChangelogPath $log
  }
}
