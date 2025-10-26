#!/bin/bash
# Kottackal Medical Store - Backup Script
# This script creates backups of the database and application files

set -e

# Configuration
BACKUP_DIR="/var/backups/kottackal"
DB_NAME="kottackal_medical_store"
DB_USER="root"
APP_DIR="/var/www/html"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

echo "============================================"
echo "Kottackal Medical Store - Backup"
echo "Started at: $(date)"
echo "============================================"
echo ""

# Backup database
echo "[1/2] Backing up database..."
DB_BACKUP="$BACKUP_DIR/db_backup_$DATE.sql"
mysqldump -u "$DB_USER" -p "$DB_NAME" > "$DB_BACKUP"
gzip "$DB_BACKUP"
echo "Database backup: ${DB_BACKUP}.gz"

# Backup application files
echo "[2/2] Backing up application files..."
APP_BACKUP="$BACKUP_DIR/app_backup_$DATE.tar.gz"
tar -czf "$APP_BACKUP" -C "$APP_DIR" \
    --exclude='logs/*' \
    --exclude='tmp/*' \
    --exclude='cache/*' \
    .
echo "Application backup: $APP_BACKUP"

# Remove backups older than 30 days
echo ""
echo "Cleaning old backups (>30 days)..."
find "$BACKUP_DIR" -name "*.gz" -type f -mtime +30 -delete
find "$BACKUP_DIR" -name "*.tar.gz" -type f -mtime +30 -delete

echo ""
echo "============================================"
echo "Backup Complete!"
echo "Completed at: $(date)"
echo "============================================"
echo ""
echo "Backup files:"
echo "  Database: ${DB_BACKUP}.gz"
echo "  Application: $APP_BACKUP"
echo ""
