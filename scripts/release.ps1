#!/usr/bin/env pwsh

# WordPress Plugin Starter - Release Script
# This script handles the complete release process with proper version management

Write-Host "[RELEASE] WordPress Plugin Starter - Release Process" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green

# Get current version
$packageJson = Get-Content "package.json" | ConvertFrom-Json
$currentVersion = $packageJson.version
Write-Host "[INFO] Current version: $currentVersion" -ForegroundColor Cyan

# Run npm version patch
Write-Host "[STEP] Running npm version patch..." -ForegroundColor Yellow
npm version patch

# Get new version
$packageJson = Get-Content "package.json" | ConvertFrom-Json
$newVersion = $packageJson.version
Write-Host "[INFO] New version: $newVersion" -ForegroundColor Cyan

# Run version sync
Write-Host "[STEP] Running version sync..." -ForegroundColor Yellow
npm run version-sync

# Add files to git
Write-Host "[STEP] Adding files to git..." -ForegroundColor Yellow
git add plugin-name.php package.json

# Create commit with proper message
$commitMessage = "Release v$newVersion"
Write-Host "[STEP] Committing with message: $commitMessage" -ForegroundColor Yellow
git commit -m $commitMessage

# Push to remote
Write-Host "[STEP] Pushing to remote..." -ForegroundColor Yellow
git push origin main --tags

Write-Host "[SUCCESS] Release completed successfully!" -ForegroundColor Green
Write-Host "[SUCCESS] Version $newVersion has been released and pushed to GitHub" -ForegroundColor Green 