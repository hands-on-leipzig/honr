# SSH Key Setup for GitHub Actions

## Problem

The error "error in libcrypto" or "Invalid SSH key format" means the SSH key in GitHub Secrets is not properly formatted.

## Solution

### Step 1: Get Your Private SSH Key

On your local machine or the server admin's machine:

```bash
# If you have an existing SSH key
cat ~/.ssh/id_rsa

# Or if using a different key
cat ~/.ssh/id_ed25519

# Or generate a new key specifically for deployment
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy
cat ~/.ssh/github_deploy
```

### Step 2: Copy the Complete Key

The key should look like this (example for OpenSSH format):

```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACD... (many more lines) ...
-----END OPENSSH PRIVATE KEY-----
```

Or for RSA format:

```
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA... (many more lines) ...
-----END RSA PRIVATE KEY-----
```

**Important:**
- ✅ Include the `-----BEGIN...-----` line
- ✅ Include the `-----END...-----` line
- ✅ Include ALL lines in between
- ✅ No extra spaces before/after
- ✅ This is the PRIVATE key (not the .pub file)

### Step 3: Add Public Key to Server

The corresponding public key needs to be on the TST server:

```bash
# On TST server, add the public key to authorized_keys
# Get the public key (from the key pair you're using)
cat ~/.ssh/id_rsa.pub
# or
cat ~/.ssh/github_deploy.pub

# On TST server:
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "YOUR_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### Step 4: Update GitHub Secret

1. Go to: https://github.com/hands-on-leipzig/honr/settings/secrets/actions
2. Click on `TST_SSH_KEY`
3. Click "Update"
4. Paste the COMPLETE private key (including BEGIN and END lines)
5. Click "Update secret"

### Step 5: Verify

The key in GitHub Secrets should:
- Start with `-----BEGIN`
- End with `-----END`
- Have no leading/trailing spaces
- Be the private key (not public)

## Common Mistakes

❌ **Wrong**: Just the key content without BEGIN/END
```
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAAB...
```

❌ **Wrong**: Public key instead of private key
```
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQC...
```

❌ **Wrong**: Key with extra spaces or line breaks

✅ **Correct**: Complete private key with headers
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAAB...
-----END OPENSSH PRIVATE KEY-----
```

## Test Locally

Before adding to GitHub, test the key works:

```bash
# Test SSH connection
ssh -i ~/.ssh/your_key user@test.honr.hands-on-technology.org

# If it works, use that key in GitHub Secrets
```
