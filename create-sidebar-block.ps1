param(
	[Parameter(Mandatory = $true)]
	[ValidatePattern('^[a-z][a-z0-9_]*$')]
	[string] $Name,

	[Parameter(Mandatory = $true)]
	[ValidatePattern('^[A-Z][A-Z0-9_]*$')]
	[string] $BlockKey,

	[Parameter(Mandatory = $true)]
	[string] $Title,

	[string] $TargetRoot = ''
)

$ErrorActionPreference = 'Stop'

$source = Split-Path -Parent $MyInvocation.MyCommand.Path
$vendorRoot = Split-Path -Parent $source

if ($TargetRoot -eq '')
{
	$TargetRoot = $vendorRoot
}

$extensionName = "sidebarblock_$Name"
$target = Join-Path $TargetRoot $extensionName
$templateName = "$Name.html"
$emptyKey = "${BlockKey}_EMPTY"
$methodName = "render_$Name"
$loopName = "sidebar_${Name}_items"
$switchVar = "S_$BlockKey"
$cssClass = "vinny-sidebarblock-$($Name -replace '_', '-')"

if (Test-Path -LiteralPath $target)
{
	throw "Target extension already exists: $target"
}

Copy-Item -LiteralPath $source -Destination $target -Recurse

$oldLanguageFile = Join-Path $target 'language\en\sidebarblock_skeleton.php'
$newLanguageFile = Join-Path $target "language\en\$extensionName.php"
Rename-Item -LiteralPath $oldLanguageFile -NewName "$extensionName.php"

$oldTemplateFile = Join-Path $target 'styles\all\template\birthdays.html'
$newTemplateFile = Join-Path $target "styles\all\template\$templateName"
Rename-Item -LiteralPath $oldTemplateFile -NewName $templateName

$files = Get-ChildItem -LiteralPath $target -Recurse -File | Where-Object {
	$_.Extension -in '.php', '.yml', '.json', '.html', '.css', '.md', '.ps1'
}

foreach ($file in $files)
{
	$content = Get-Content -LiteralPath $file.FullName -Raw
	$content = $content.Replace('sidebarblock_skeleton', $extensionName)
	$content = $content.Replace('sidebarblock-skeleton', "sidebarblock-$($Name -replace '_', '-')")
	$content = $content.Replace('Sidebar Block Skeleton', $Title)
	$content = $content.Replace('SIDEBARBLOCK_SKELETON_REQUIRES_SIDEBAR', "SIDEBARBLOCK_$($Name.ToUpperInvariant())_REQUIRES_SIDEBAR")
	$content = $content.Replace('SIDEBAR_SKELETON_BIRTHDAYS_EMPTY', $emptyKey)
	$content = $content.Replace('SIDEBAR_SKELETON_BIRTHDAYS', $BlockKey)
	$content = $content.Replace('S_SIDEBARBLOCK_SKELETON_BIRTHDAYS', $switchVar)
	$content = $content.Replace('sidebarblock_skeleton_birthdays', $loopName)
	$content = $content.Replace('vinny-sidebarblock-skeleton-birthdays', $cssClass)
	$content = $content.Replace('birthdays.html', $templateName)
	$content = $content.Replace('render_birthdays', $methodName)
	Set-Content -LiteralPath $file.FullName -Value $content -NoNewline
}

Write-Host "Created $extensionName at $target"
Write-Host "Next: replace the example renderer logic in event/listener.php, then purge phpBB cache."
