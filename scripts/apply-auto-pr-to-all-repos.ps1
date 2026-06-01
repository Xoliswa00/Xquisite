<#
.SYNOPSIS
    Pushes the auto-PR GitHub Actions workflow to every repo you own.

.DESCRIPTION
    Reads your GitHub PAT from the GITHUB_TOKEN env var (or prompts for it),
    lists all repos owned by GITHUB_USER, and creates or updates
    .github/workflows/auto-pr.yml in each one via the GitHub API.

    Skips repos where the workflow already exists and is identical.
    Skips archived repos.

.USAGE
    $env:GITHUB_TOKEN = "github_pat_xxxx"
    .\scripts\apply-auto-pr-to-all-repos.ps1

    # Dry run (lists repos but makes no changes):
    .\scripts\apply-auto-pr-to-all-repos.ps1 -DryRun

    # Only apply to repos whose names match a pattern:
    .\scripts\apply-auto-pr-to-all-repos.ps1 -Filter "Xquisite"
#>

param(
    [switch] $DryRun,
    [string] $Filter = "",
    [string] $GitHubUser = "MrX-Studio99",
    [string] $BaseBranch = "",        # leave blank to use each repo's default branch
    [string] $CommitMessage = "ci: add auto-PR workflow"
)

# ── Auth ──────────────────────────────────────────────────────────────────────
$token = $env:GITHUB_TOKEN
if (-not $token) {
    $secureToken = Read-Host "GitHub Personal Access Token (repo scope)" -AsSecureString
    $token = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secureToken)
    )
}

$headers = @{
    Authorization = "Bearer $token"
    Accept        = "application/vnd.github+json"
    "X-GitHub-Api-Version" = "2022-11-28"
}

# ── Workflow file content (base64-encoded for the API) ────────────────────────
$workflowYaml = @'
name: Auto-create draft PR

on:
  push:
    branches-ignore:
      - main
      - dev
      - master

permissions:
  pull-requests: write
  contents: read

jobs:
  open-pr:
    runs-on: ubuntu-latest
    steps:
      - name: Create draft PR if none exists
        env:
          GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
          BRANCH="${{ github.ref_name }}"
          REPO="${{ github.repository }}"

          EXISTING=$(gh pr list --repo "$REPO" --head "$BRANCH" --json number --jq 'length')

          if [ "$EXISTING" = "0" ]; then
            TITLE=$(echo "$BRANCH" \
              | sed 's|.*/||'        \
              | tr '-' ' '           \
              | awk '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) substr($i,2); print}')

            gh pr create \
              --repo    "$REPO" \
              --base    "$(gh repo view "$REPO" --json defaultBranchRef --jq '.defaultBranchRef.name' 2>/dev/null || echo 'main')" \
              --head    "$BRANCH" \
              --title   "$TITLE" \
              --body    "## Summary

> _Auto-created draft PR — fill in what this branch does before merging._

## Test plan

- [ ] Tested locally
- [ ] No regressions in related features

🤖 Auto-opened by [auto-pr workflow](/.github/workflows/auto-pr.yml)" \
              --draft
            echo "Draft PR created for $BRANCH"
          else
            echo "PR already exists for $BRANCH — skipping"
          fi
'@

$workflowBase64 = [Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes($workflowYaml))
$workflowPath   = ".github/workflows/auto-pr.yml"

# ── Fetch all repos ───────────────────────────────────────────────────────────
Write-Host "`nFetching repos for $GitHubUser..." -ForegroundColor Cyan

$allRepos = @()
$page = 1
do {
    $url  = "https://api.github.com/user/repos?per_page=100&page=$page&affiliation=owner"
    $page_repos = Invoke-RestMethod -Uri $url -Headers $headers -Method Get
    $allRepos += $page_repos
    $page++
} while ($page_repos.Count -eq 100)

if ($Filter) {
    $allRepos = $allRepos | Where-Object { $_.name -like "*$Filter*" }
}

$activeRepos = $allRepos | Where-Object { -not $_.archived }

Write-Host "Found $($activeRepos.Count) active repo(s)$(if ($Filter) { " matching '$Filter'" })." -ForegroundColor Cyan
if ($DryRun) { Write-Host "[DRY RUN — no changes will be made]`n" -ForegroundColor Yellow }

# ── Apply to each repo ────────────────────────────────────────────────────────
$applied = 0
$skipped = 0
$failed  = 0

foreach ($repo in $activeRepos) {
    $repoFullName = $repo.full_name
    $branch = if ($BaseBranch) { $BaseBranch } else { $repo.default_branch }

    Write-Host "`n[$repoFullName]" -NoNewline

    # Check if file already exists
    $fileUrl = "https://api.github.com/repos/$repoFullName/contents/$workflowPath"
    try {
        $existing = Invoke-RestMethod -Uri $fileUrl -Headers $headers -Method Get -ErrorAction Stop
        $existingContent = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String($existing.content -replace "`n",""))

        if ($existingContent.Trim() -eq $workflowYaml.Trim()) {
            Write-Host " already up to date — skipped" -ForegroundColor DarkGray
            $skipped++
            continue
        }

        # File exists but is different — update it
        if ($DryRun) {
            Write-Host " would UPDATE existing workflow" -ForegroundColor Yellow
            $skipped++
            continue
        }

        $body = @{
            message = $CommitMessage
            content = $workflowBase64
            sha     = $existing.sha
            branch  = $branch
        } | ConvertTo-Json

        Invoke-RestMethod -Uri $fileUrl -Headers $headers -Method Put -Body $body -ContentType "application/json" | Out-Null
        Write-Host " updated" -ForegroundColor Green
        $applied++

    } catch {
        if ($_.Exception.Response.StatusCode -eq 404) {
            # File doesn't exist — create it
            if ($DryRun) {
                Write-Host " would CREATE workflow" -ForegroundColor Yellow
                $skipped++
                continue
            }

            $body = @{
                message = $CommitMessage
                content = $workflowBase64
                branch  = $branch
            } | ConvertTo-Json

            try {
                Invoke-RestMethod -Uri $fileUrl -Headers $headers -Method Put -Body $body -ContentType "application/json" | Out-Null
                Write-Host " created" -ForegroundColor Green
                $applied++
            } catch {
                Write-Host " FAILED: $($_.Exception.Message)" -ForegroundColor Red
                $failed++
            }
        } else {
            Write-Host " FAILED: $($_.Exception.Message)" -ForegroundColor Red
            $failed++
        }
    }
}

# ── Summary ───────────────────────────────────────────────────────────────────
Write-Host "`n─────────────────────────────────"
Write-Host "Applied : $applied repo(s)" -ForegroundColor Green
Write-Host "Skipped : $skipped repo(s)" -ForegroundColor DarkGray
if ($failed -gt 0) {
    Write-Host "Failed  : $failed repo(s)" -ForegroundColor Red
}
Write-Host "─────────────────────────────────`n"
