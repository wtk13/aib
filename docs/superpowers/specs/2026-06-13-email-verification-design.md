# Email Verification — Design Spec

**Date:** 2026-06-13
**Status:** Approved

## Goal

Block panel access until a newly registered user confirms their email address. After registration, a verification email is sent automatically. Clicking the link redirects the user to the dashboard.

## Changes

### 1. Migration — `add_email_verified_at_to_users_table`

Add a nullable `timestamp` column `email_verified_at` to the `users` table.

### 2. `User` model

- Implement `Illuminate\Contracts\Auth\MustVerifyEmail`
- Update `canAccessPanel()` to require both `role === 'owner'` AND `hasVerifiedEmail()`

### 3. `AppPanelProvider`

- Add `->emailVerification()` to the panel chain. This enables Filament's built-in prompt and resend pages at `/admin/email-verification/prompt` and `/admin/email-verification/send`.

### 4. `Register::handleRegistration()`

- After user creation, call `$user->sendEmailVerificationNotification()` to dispatch the verification email immediately on registration.

### 5. `.env.example`

- Change `MAIL_FROM_ADDRESS` from `hello@example.com` to `noreply@tbasystem.pl`

## Flow

```
Register → user created → sendEmailVerificationNotification()
→ user tries to access panel → blocked, redirected to /admin/email-verification/prompt
→ user clicks link in email → verified, redirected to dashboard
```

## Out of scope

- Custom email template (Laravel default is sufficient for v1)
- Additional roles beyond `owner`
- Email change + re-verification flow
