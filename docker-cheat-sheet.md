# 🐳 Docker Commands Cheat Sheet - FoodHub Backend

**Multi-channel SaaS platform for restaurants** - Docker container management guide

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15+-blue.svg)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-compose-blue.svg)](https://docker.com)

---

## 📋 Container Access Commands

### 🔗 Direct Container Access
```bash
# PHP-FPM (Laravel Application) - Alpine
docker exec -it foodhub-php-fpm-1 /bin/sh

# PostgreSQL Database - Debian
docker exec -it foodhub-postgres-1 /bin/bash

# Redis Cache & Sessions - Alpine  
docker exec -it foodhub-redis-1 /bin/sh

# Nginx Web Server - Alpine
docker exec -it foodhub-nginx-1 /bin/sh

# ClickHouse Analytics DB - Debian
docker exec -it foodhub-clickhouse-1 /bin/bash

# Soketi WebSocket Server - Alpine
docker exec -it foodhub-soketi-1 /bin/sh
```

---

## 🚀 Basic Operations

### Container Management
```bash
# Start all services
docker compose start

# Stop all services  
docker compose stop

# Restart all services
docker compose restart

# View running containers
docker compose ps

# View logs for all services
docker compose logs

# View logs for specific service
docker compose logs foodhub-php-fpm-1
docker compose logs foodhub-postgres-1
docker compose logs foodhub-nginx-1

# Follow logs in real-time
docker compose logs -f foodhub-php-fpm-1
```

### Service Health Check
```bash
# Check container status
docker compose ps --services

# Check resource usage
docker stats

# Inspect specific container
docker inspect foodhub-php-fpm-1
```

---

## 🔧 Development & Debugging

### 🐘 PHP-FPM Container (Laravel)
```bash
# Access PHP-FPM container
docker exec -it foodhub-php-fpm-1 /bin/sh

# Inside container - Laravel commands:
php artisan tinker                    # Interactive shell
php artisan migrate                   # Run migrations
php artisan migrate:fresh --seed     # Fresh migration with seeds
php artisan cache:clear              # Clear application cache
php artisan config:clear             # Clear config cache
php artisan route:list               # List all routes
php artisan queue:work               # Process queue jobs
php artisan l5-swagger:generate      # Generate API docs

# Composer commands:
composer install                     # Install dependencies
composer update                      # Update dependencies
composer dump-autoload              # Regenerate autoloader

# File permissions:
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
```

### 🌐 Nginx Container
```bash
# Access Nginx container
docker exec -it foodhub-nginx-1 /bin/sh

# Inside container - Nginx commands:
nginx -t                             # Test configuration
nginx -s reload                      # Reload configuration
cat /etc/nginx/nginx.conf           # View main config
cat /etc/nginx/conf.d/default.conf  # View site config

# View logs:
tail -f /var/log/nginx/access.log   # Access logs
tail -f /var/log/nginx/error.log    # Error logs
```

---

## 🗄️ Database Operations

### 🐘 PostgreSQL Container
```bash
# Access PostgreSQL container
docker exec -it foodhub-postgres-1 /bin/bash

# Inside container - PostgreSQL commands:
psql -U foodhub -d foodhub          # Connect to database
psql -U foodhub -d foodhub -c "\dt"  # List tables
psql -U foodhub -d foodhub -c "\d users" # Describe users table

# Database operations:
pg_dump -U foodhub foodhub > backup.sql     # Create backup
psql -U foodhub foodhub < backup.sql        # Restore backup
createdb -U foodhub new_database             # Create new database
dropdb -U foodhub old_database               # Drop database

# Monitoring:
psql -U foodhub -d foodhub -c "SELECT * FROM pg_stat_activity;" # Active connections
```

### 🔴 Redis Container
```bash
# Access Redis container
docker exec -it foodhub-redis-1 /bin/sh

# Inside container - Redis commands:
redis-cli                           # Connect to Redis
redis-cli ping                      # Test connection
redis-cli info                      # Server information
redis-cli monitor                   # Monitor commands in real-time

# Key operations:
redis-cli keys "*"                  # List all keys
redis-cli get "key_name"            # Get key value  
redis-cli del "key_name"            # Delete key
redis-cli flushall                  # Clear all data
redis-cli dbsize                    # Number of keys
```

### 📊 ClickHouse Container
```bash
# Access ClickHouse container
docker exec -it foodhub-clickhouse-1 /bin/bash

# Inside container - ClickHouse commands:
clickhouse-client                    # Connect to ClickHouse
clickhouse-client --query "SHOW DATABASES;" # List databases
clickhouse-client --query "SHOW TABLES;" # List tables

# Query examples:
clickhouse-client --query "SELECT count() FROM system.numbers LIMIT 10;"
clickhouse-client --query "SELECT version();" # Version info
```

---

## 📁 File Operations

### Copy Files To/From Containers
```bash
# Copy file TO container
docker cp ./local-file.txt foodhub-php-fpm-1:/var/www/html/

# Copy file FROM container  
docker cp foodhub-php-fpm-1:/var/www/html/storage/logs/laravel.log ./

# Copy entire directory
docker cp ./vendor/ foodhub-php-fpm-1:/var/www/html/

# Backup database
docker exec foodhub-postgres-1 pg_dump -U foodhub foodhub > backup.sql
```

### Log File Locations
```bash
# Laravel logs (PHP-FPM container)
docker exec foodhub-php-fpm-1 tail -f /var/www/html/storage/logs/laravel.log

# Nginx logs
docker exec foodhub-nginx-1 tail -f /var/log/nginx/access.log
docker exec foodhub-nginx-1 tail -f /var/log/nginx/error.log

# PostgreSQL logs  
docker exec foodhub-postgres-1 tail -f /var/log/postgresql/postgresql-15-main.log
```

---

## 📊 Monitoring & Performance

### Resource Monitoring
```bash
# Real-time container stats
docker stats

# Specific container stats
docker stats foodhub-php-fpm-1

# Container resource limits
docker inspect foodhub-php-fpm-1 | grep -i memory
docker inspect foodhub-postgres-1 | grep -i cpu
```

### Health Checks
```bash
# Check if services are responding
curl http://localhost                # Nginx
curl http://localhost/api/v1/restaurants # API endpoint

# Database connection test
docker exec foodhub-php-fpm-1 php artisan tinker
# Inside tinker: DB::connection()->getPdo()

# Redis connection test  
docker exec foodhub-redis-1 redis-cli ping
```

---

## 🌐 Service Information

### 🔗 Access Points
- **API Base URL**: `http://localhost/api/v1`
- **Swagger Documentation**: `http://localhost/api/documentation`
- **Main Website**: `http://localhost`

### 📡 Internal Ports
- **Nginx**: 80 (HTTP)
- **PostgreSQL**: 5432
- **Redis**: 6379
- **ClickHouse**: 8123 (HTTP), 9000 (Native)
- **Soketi**: 6001

### 🛠️ Service Roles
- **🌐 Nginx** - Web server & reverse proxy
- **🐘 PHP-FPM** - Laravel application server
- **🗄️ PostgreSQL** - Primary database
- **🔴 Redis** - Cache, sessions & queues
- **📊 ClickHouse** - Analytics & reporting
- **⚡ Soketi** - WebSocket server for real-time features

---

## 🔧 Troubleshooting

### Common Issues
```bash
# Permission issues
docker exec foodhub-php-fpm-1 chown -R www-data:www-data /var/www/html/storage
docker exec foodhub-php-fpm-1 chmod -R 775 /var/www/html/storage

# Clear all caches
docker exec foodhub-php-fpm-1 php artisan cache:clear
docker exec foodhub-php-fpm-1 php artisan config:clear
docker exec foodhub-php-fpm-1 php artisan view:clear

# Restart specific service
docker compose restart foodhub-php-fpm-1
docker compose restart foodhub-nginx-1

# View container logs for debugging
docker compose logs foodhub-php-fpm-1 --tail=50
docker compose logs foodhub-postgres-1 --tail=50
```

### Emergency Commands
```bash
# Force stop all containers
docker compose down

# Remove all containers and volumes (⚠️ DATA LOSS)
docker compose down -v

# Rebuild specific container
docker compose up --build foodhub-php-fpm-1

# Reset everything (⚠️ NUCLEAR OPTION)
docker compose down -v --remove-orphans
docker compose up -d --build
```

---

## 📚 Quick Reference

### Most Used Commands
```bash
# Daily development workflow:
docker compose start                          # Start environment
docker exec -it foodhub-php-fpm-1 /bin/sh   # Access Laravel app
docker exec -it foodhub-postgres-1 /bin/bash # Access database
docker compose logs -f foodhub-php-fpm-1    # Monitor logs

# Database quick access:
docker exec -it foodhub-postgres-1 psql -U foodhub -d foodhub

# Cache operations:
docker exec -it foodhub-redis-1 redis-cli
docker exec foodhub-php-fpm-1 php artisan cache:clear
```

---

## 🔐 Authentication & Testing

### Create Test User Token
```bash
# Access Laravel Tinker
docker exec -it foodhub-php-fpm-1 php artisan tinker

# Inside Tinker:
$user = App\Models\User::first();
$token = $user->createToken('API Token')->accessToken;
echo $token;
```

### Test API Endpoints
```bash
# Test with curl (replace YOUR_TOKEN)
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost/api/v1/auth/me
curl http://localhost/api/v1/restaurants
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost/api/v1/orders
```

---

**🚀 Happy Coding with FoodHub Backend!**

*Remember: Always backup your data before running destructive commands* 