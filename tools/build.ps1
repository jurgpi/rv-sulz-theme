<#
  RV Sulz Theme - Build & Versionierung

  Aufruf (im Ordner CustomTemplate):
    powershell -ExecutionPolicy Bypass -File tools\build.ps1                 # aktuelle Version bauen
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump patch     # 1.4.5 -> 1.4.6 (Fehlerbehebung)
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump minor     # 1.4.5 -> 1.5.0 (neue Funktion)
    powershell -ExecutionPolicy Bypass -File tools\build.ps1 -Bump major     # 1.4.5 -> 2.0.0 (grosser Umbau)

  - Die Version steht nur im Kopf von rv-sulz\style.css.
  - Bei -Bump wird in CHANGELOG.md ein neuer Abschnitt angelegt (bitte danach ausfuellen).
  - Ergebnis: dist\rv-sulz-<version>.zip  (Ordner im ZIP heisst immer "rv-sulz",
    damit WordPress beim Hochladen das bestehende Theme ersetzt).
  - Eine bereits gebaute Version wird nicht ueberschrieben (ausser mit -Force), damit nie
    zwei verschiedene Staende dieselbe Nummer tragen.

  Git (optional):
    ... build.ps1 -Release          # alle Aenderungen als "Version x.y.z" committen + Tag vx.y.z
    ... build.ps1 -Release -Push    # zusaetzlich zu GitHub hochladen (Commit + Tag)
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
  [switch]$Push
)

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
  }
}
