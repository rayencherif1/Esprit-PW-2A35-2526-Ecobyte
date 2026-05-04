$root = $PSScriptRoot
$prefix = "http://127.0.0.1:8080/"
$listener = New-Object System.Net.HttpListener
$listener.Prefixes.Add($prefix)

try {
  $listener.Start()
} catch {
  Write-Host "Could not start server (try another port or run as admin): $_"
  exit 1
}

Write-Host "Serving $root at $prefix"
Write-Host "Press Ctrl+C to stop."

$mimes = @{
  ".html" = "text/html; charset=utf-8"
  ".css"  = "text/css; charset=utf-8"
  ".js"   = "application/javascript; charset=utf-8"
  ".json" = "application/json; charset=utf-8"
  ".svg"  = "image/svg+xml"
  ".ico"  = "image/x-icon"
}

while ($listener.IsListening) {
  $ctx = $listener.GetContext()
  $req = $ctx.Request
  $res = $ctx.Response
  try {
    $path = [Uri]::UnescapeDataString($req.Url.AbsolutePath.TrimStart("/"))
    if ([string]::IsNullOrWhiteSpace($path)) { $path = "index.html" }
    $fullPath = [System.IO.Path]::GetFullPath((Join-Path $root ($path -replace "/", [IO.Path]::DirectorySeparatorChar)))

    if (-not $fullPath.StartsWith($root, [System.StringComparison]::OrdinalIgnoreCase)) {
      $res.StatusCode = 403
    }
    elseif (-not (Test-Path -LiteralPath $fullPath -PathType Leaf)) {
      $res.StatusCode = 404
    }
    else {
      $bytes = [System.IO.File]::ReadAllBytes($fullPath)
      $ext = [System.IO.Path]::GetExtension($fullPath).ToLowerInvariant()
      $res.ContentType = $mimes[$ext]
      if (-not $res.ContentType) { $res.ContentType = "application/octet-stream" }
      $res.ContentLength64 = $bytes.LongLength
      $res.OutputStream.Write($bytes, 0, $bytes.Length)
    }
  }
  finally {
    $res.Close()
  }
}
