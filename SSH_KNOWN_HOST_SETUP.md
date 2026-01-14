# SSH_KNOWN_HOST Setup

## What to Use

When running `ssh-keyscan -H test.honr.hands-on-technology.org`, you'll get output like:

```
# test.honr.hands-on-technology.org:22 SSH-2.0-mod_sftp
|1|acHmubsjjYMc9kHm0mmb6fOINtk=|P2DuJemE9gUWD7ccwhbrS9JOOyo= ssh-rsa AAAAB3NzaC1yc2E...
# test.honr.hands-on-technology.org:22 SSH-2.0-mod_sftp
|1|pzD32TVV35B4q5wcDAmQD4daojY=|njWVZQYSDAm+9nE4hPw1Y/AEnVM= ssh-ed25519 AAAAC3NzaC1lZDI1NTE5...
```

## What to Copy

**Copy ALL lines that start with `|1|` or `test.honr`** (the actual key lines, not the comment lines starting with `#`).

For example, use these lines:
```
|1|acHmubsjjYMc9kHm0mmb6fOINtk=|P2DuJemE9gUWD7ccwhbrS9JOOyo= ssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEA6zc5l5Q5X1Dcs2Xeewgn4QeWudvU3ipr28oevwp0700qD9pd59Vevge//2rD/YHBTuGWrOUT38EXpfNNNm+cQOxua7en/lJMlNt8ssrn9c8CoTCRhugL7r4f8C3cG+MybNSfPscnLSHL67++x9GuvEqnekhAB1BIghhT+VVD3Ca4yHJFmrQehptUrbKHAs9MtmZoFvnw2WJoKGljarCwioJHwFXrX52oSmuoJyGaoUKziv5f+bufYrubI02w5E1y4F6r8uONxlGOt6nBE8ubyvgWNQ7oarejAAVscjBkEW8wqJNbWXDcQcpeqPwQooeAKi8n2hbUQA/O9il0Qrm86Q==
|1|pzD32TVV35B4q5wcDAmQD4daojY=|njWVZQYSDAm+9nE4hPw1Y/AEnVM= ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIF6Wxbp9Y0EFEUikDYS5QtJjQU6kKbcK4UU02smRJ9zp
```

## Quick Command

To get just the key lines (without comments):

```bash
ssh-keyscan -H test.honr.hands-on-technology.org 2>&1 | grep -v "^#"
```

This will output only the key lines, which you can copy directly into the GitHub Secret.

## Add to GitHub

1. Run: `ssh-keyscan -H test.honr.hands-on-technology.org 2>&1 | grep -v "^#"`
2. Copy the entire output (all lines)
3. Go to: GitHub → Settings → Secrets and variables → Actions
4. Add new secret: `SSH_KNOWN_HOST`
5. Paste the entire output (all key lines)
6. Save

## Important

- Include ALL key lines (RSA, ED25519, etc.)
- Don't include comment lines (starting with `#`)
- The output should be multiple lines, one per key type
