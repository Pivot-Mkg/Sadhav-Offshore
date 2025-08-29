# Sadhav Offshore Corporate Website

This repository contains the source code for the official Sadhav Offshore corporate website, built with semantic HTML5, modular CSS, vanilla JavaScript, and a PHP backend for form processing.

## Tech Stack

-   **Frontend:** HTML5, CSS3 (with CSS Variables), Vanilla JavaScript (ES6+)
-   **Backend (Forms):** PHP 8.x
-   **Deployment:** Standard Apache/Nginx hosting environment.

## Project Structure

```
/
├── assets/
│   ├── css/
│   └── js/
├── forms/
│   ├── contact.php
│   ├── job-apply.php
│   ├── rfq.php
│   ├── mailer.php
│   ├── validator.php
│   ├── token.php
│   └── config.php.sample
├── uploads/
│   └── .htaccess
├── .htaccess
├── index.html
└── ... (all other HTML pages)
```

## Setup & Configuration

### 1. Web Server

-   Ensure you have a web server (like Apache or Nginx) with PHP support (8.0 or higher).
-   Point your web server's document root to the project's root directory.
-   The `.htaccess` file includes basic configurations for security and performance. Ensure your server allows `.htaccess` overrides.
-   The `/uploads` directory must be writable by the web server for resume/document uploads to function.

### 2. Form Configuration

All forms require configuration to send emails. This is managed in a central configuration file.

1.  **Create the config file:** In the `/forms` directory, rename `config.php.sample` to `config.php`.
2.  **Edit `config.php`:** Open the new `config.php` file and set the recipient email address in the `TO_EMAIL` constant.
3.  **Customize Subjects:** You can customize the email subject lines for each form within their respective PHP handlers (`contact.php`, `job-apply.php`, etc.).

For enhanced security and reliability in a production environment, it is highly recommended to:
-   **Implement reCAPTCHA v3:** The project includes placeholders for reCAPTCHA. Add your site keys to `config.php` and integrate the validation logic in the PHP form handlers.
-   **Use SMTP:** For more reliable email delivery, use an SMTP library like PHPMailer instead of the default `mail()` function. The `config.php` file has placeholder constants for SMTP credentials.

### 3. Running Lighthouse & Accessibility Checks

-   Open your website in Google Chrome.
-   Open Chrome DevTools (`Ctrl+Shift+I` or `Cmd+Opt+I`).
-   Navigate to the "Lighthouse" tab.
-   Select categories (Performance, Accessibility, Best Practices, SEO) and "Desktop" or "Mobile".
-   Click "Analyze page load".
-   Review the report and address any identified issues. The goal is to achieve scores of 90+ in all categories.
-   Manually test keyboard navigation (using Tab, Shift+Tab, Enter) and screen reader compatibility (using NVDA, VoiceOver, or JAWS).

## Style Guide

A basic style guide is available at `styleguide.html`. It documents the brand's color palette, typography, and common UI components like buttons and cards, serving as a reference for developers and designers.