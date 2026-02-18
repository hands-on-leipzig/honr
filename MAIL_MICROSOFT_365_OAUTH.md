# Sending Email via Microsoft 365 with OAuth (Graph API)

## Why not username + password?

**Microsoft is retiring Basic Authentication for SMTP** in Exchange Online:

- **Timeline:** Gradual rejection from March 2026, **100% rejection by April 2026**.
- **Effect:** `smtp.office365.com` with `MAIL_USERNAME` + `MAIL_PASSWORD` will stop working; you’ll see errors like *"Basic authentication is not supported for Client Submission"*.
- **Required:** Use **OAuth** (token-based auth). The app uses the **Microsoft Graph API** with an **Azure App (client credentials)** instead of SMTP + user/password.

So: **no, you can’t rely on user + password with Microsoft 365 anymore**; it needs to be OAuth (we use Graph API with client ID + secret + tenant).

## What’s in the codebase

- **Package:** [innoge/laravel-msgraph-mail](https://github.com/InnoGE/laravel-msgraph-mail) – Laravel mail driver that sends via Microsoft Graph API using Azure App credentials (no user password, no refresh token).
- **Config:** `config/mail.php` defines a `microsoft-graph` mailer.
- **Env:** `env.tst.example` and `env.prd.example` use `MAIL_MAILER=microsoft-graph` and the Graph env vars below.

## Setup (Azure + .env)

### 1. Azure App registration

1. In **Azure Portal** → **Microsoft Entra ID (Azure AD)** → **App registrations** → **New registration**.
2. Name the app (e.g. “HONR Mail”), choose **Accounts in this organizational directory only**, register.
3. **Certificates & secrets** → **New client secret** → copy the **Value** (client secret); store it securely.
4. **API permissions** → **Add permission** → **Microsoft Graph** → **Application permissions** → add **Mail.Send** → **Grant admin consent**.
5. Note:
   - **Application (client) ID**
   - **Directory (tenant) ID**
   - **Client secret value**

The app will send mail **as** a mailbox in your tenant. You must grant that mailbox “send as” or use a shared mailbox the app is allowed to send from (see Microsoft docs). The “from” address in Laravel must be a valid mailbox in the same tenant.

### 2. .env on the server

Use the **microsoft-graph** driver and the Azure App credentials (no user/password):

```env
MAIL_MAILER=microsoft-graph
MICROSOFT_GRAPH_CLIENT_ID=your-application-client-id
MICROSOFT_GRAPH_CLIENT_SECRET=your-client-secret-value
MICROSOFT_GRAPH_TENANT_ID=your-tenant-id
MAIL_FROM_ADDRESS="noreply@hands-on-technology.org"
MAIL_FROM_NAME="${APP_NAME}"
MAIL_SAVE_TO_SENT_ITEMS=false
```

- **MICROSOFT_GRAPH_CLIENT_ID** = Application (client) ID from Azure.
- **MICROSOFT_GRAPH_CLIENT_SECRET** = Client secret value (the secret itself, not its ID).
- **MICROSOFT_GRAPH_TENANT_ID** = Directory (tenant) ID.
- **MAIL_FROM_ADDRESS** = Must be a mailbox in your tenant that the app is allowed to send as.

Then:

```bash
php artisan config:clear
php artisan config:cache
```

### 3. Sender mailbox

Graph sends mail **as** a user/mailbox. With **Application permission** `Mail.Send`, you typically:

- Use a **shared mailbox** and assign the app permission to send as that mailbox, or  
- Configure the app to send as a specific user (see [Microsoft Graph “Send mail”](https://learn.microsoft.com/en-us/graph/api/user-sendmail) and app-only access).

The package’s [blog post](https://geisi.dev/blog/getting-rid-of-deprecated-microsoft-office-365-smtp-mail-sending) and [Microsoft Graph Mail.Send](https://learn.microsoft.com/en-us/graph/permissions-reference#mail-permissions) describe the exact consent and mailbox setup.

## Summary

| Old (deprecated)        | New (this app)                    |
|-------------------------|-----------------------------------|
| SMTP + user + password  | Microsoft Graph API + OAuth      |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | `MICROSOFT_GRAPH_CLIENT_ID` / `_CLIENT_SECRET` / `_TENANT_ID` |
| `MAIL_MAILER=smtp`      | `MAIL_MAILER=microsoft-graph`     |

So: **nothing in the app is built for “user + password” with MS 365 long-term**; it’s prepared for **OAuth via the Microsoft Graph mail driver** and the env vars above.
