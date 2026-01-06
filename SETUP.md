# Setup Instructions

## 1. Install Dependencies

### PHP Dependencies
```bash
composer install
```

### JavaScript Dependencies
```bash
npm install
```

## 2. Publish Spatie Permissions Migration

Run the following command to publish the Spatie Laravel Permission migrations:

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 3. Run Migrations

```bash
php artisan migrate
```

## 4. Run Seeders

```bash
php artisan db:seed
```

This will create:
- Admin role
- Admin user with:
  - Name: hamza
  - Email: admin@gmail.com
  - Password: 12345678
  - Role: admin

## 5. Add Logo Image

Place your Dravion Enterprise logo image in `public/images/dravion-logo.png`

The logo should be:
- PNG format (transparent background recommended)
- High resolution (at least 200px height)
- Named exactly: `dravion-logo.png`

## 6. Build Assets

```bash
npm run build
```

Or for development:

```bash
npm run dev
```

## 7. Start the Server

```bash
php artisan serve
```

## 8. Access Login Page

Navigate to: `http://localhost:8000/login`

Login with:
- Email: `admin@gmail.com`
- Password: `12345678`

## Notes

- The login page uses Vue 3 with shadcn-vue styled components
- Spatie Laravel Permission is configured for role-based access control
- The User model includes a `role` field and uses Spatie's `HasRoles` trait
- All authentication is handled through Laravel's built-in authentication system

