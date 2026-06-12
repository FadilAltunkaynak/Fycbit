# Backup and Restore

Never store production backups in GitHub.

## Development Database Backup

```bash
mysqldump --single-transaction --routines --events --triggers \
  -u fycbit -p fycbit | gzip > fycbit-development.sql.gz
sha256sum fycbit-development.sql.gz > fycbit-development.sql.gz.sha256
```

## Development Restore

Restore only into a new empty database:

```bash
gzip -dc fycbit-development.sql.gz | mysql -u fycbit -p fycbit_restore
```

After restore:

```bash
cd apps/backend
php artisan migrate:status
php artisan config:clear
php artisan cache:clear
```

Production backup design must separately cover database, uploads, private
configuration, wallet state, TLS material, and encryption keys. Store encrypted
copies in at least two failure domains and perform scheduled restore drills.

